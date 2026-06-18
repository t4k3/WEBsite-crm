// removebg.swift — Rimozione sfondo on-device (Vision, macOS 14+)
// Uso:  swift removebg.swift <input> <output.png> [--crop] [--erode N]
//   --crop    ritaglia stretto attorno al soggetto
//   --erode N raggio di erosione del bordo in px (default: auto ~0.18% larghezza)
//             elimina l'alone di colore di sfondo sui bordi del cutout.
// Produce un PNG con sfondo trasparente.

import Foundation
import Vision
import CoreImage
import AppKit

func die(_ s: String, _ c: Int32) -> Never { FileHandle.standardError.write((s + "\n").data(using: .utf8)!); exit(c) }

let args = CommandLine.arguments
guard args.count >= 3 else { die("Uso: removebg.swift <input> <output.png> [--crop] [--erode N]", 1) }
let inputPath = args[1], outputPath = args[2]
let crop = args.contains("--crop")
var erodePx: Double = -1
if let i = args.firstIndex(of: "--erode"), i + 1 < args.count { erodePx = Double(args[i + 1]) ?? -1 }

guard let input = CIImage(contentsOf: URL(fileURLWithPath: inputPath),
                          options: [.applyOrientationProperty: true]) else { die("Impossibile leggere \(inputPath)", 1) }

let handler = VNImageRequestHandler(ciImage: input, options: [:])
let request = VNGenerateForegroundInstanceMaskRequest()
do { try handler.perform([request]) } catch { die("Errore Vision: \(error)", 5) }
guard let result = request.results?.first else { die("Nessun soggetto rilevato in \(inputPath)", 2) }

// Maschera a piena risoluzione (single channel)
let maskPB: CVPixelBuffer
do { maskPB = try result.generateScaledMaskForImage(forInstances: result.allInstances, from: handler) }
catch { die("Errore maschera: \(error)", 3) }

var mask = CIImage(cvPixelBuffer: maskPB)
let sx = input.extent.width / mask.extent.width
let sy = input.extent.height / mask.extent.height
if abs(sx - 1) > 0.001 || abs(sy - 1) > 0.001 {
    mask = mask.transformed(by: CGAffineTransform(scaleX: sx, y: sy))
}
mask = mask.cropped(to: input.extent)

// Erosione del bordo per togliere l'alone di colore di sfondo, + lieve AA
let autoErode = max(2.0, input.extent.width * 0.0018)
let r = erodePx >= 0 ? erodePx : autoErode
if r > 0 {
    mask = mask.applyingFilter("CIMorphologyMinimum", parameters: [kCIInputRadiusKey: r])
        .applyingFilter("CIGaussianBlur", parameters: [kCIInputRadiusKey: max(0.5, r * 0.22)])
        .cropped(to: input.extent)
}

// Applica la maschera come canale alpha
let clear = CIImage(color: CIColor(red: 0, green: 0, blue: 0, alpha: 0)).cropped(to: input.extent)
let out = input.applyingFilter("CIBlendWithMask", parameters: [
    kCIInputBackgroundImageKey: clear,
    kCIInputMaskImageKey: mask,
])

let ctx = CIContext()

// Bounding box del soggetto per --crop (scansione su maschera ridotta)
func contentBBox(_ m: CIImage) -> CGRect {
    let maxDim: CGFloat = 400
    let s = min(1, maxDim / max(m.extent.width, m.extent.height))
    let small = m.transformed(by: CGAffineTransform(scaleX: s, y: s))
    guard let cg = ctx.createCGImage(small, from: small.extent) else { return m.extent }
    let rep = NSBitmapImageRep(cgImage: cg)
    let w = rep.pixelsWide, h = rep.pixelsHigh
    var minX = w, minY = h, maxX = -1, maxY = -1
    for yy in 0..<h {
        for xx in 0..<w where (rep.colorAt(x: xx, y: yy)?.brightnessComponent ?? 0) > 0.5 {
            if xx < minX { minX = xx }; if xx > maxX { maxX = xx }
            if yy < minY { minY = yy }; if yy > maxY { maxY = yy }
        }
    }
    if maxX < minX { return m.extent }
    let fx = m.extent.width / CGFloat(w), fy = m.extent.height / CGFloat(h)
    let pad: CGFloat = 8
    let x0 = max(0, CGFloat(minX) * fx - pad)
    let x1 = min(m.extent.width, CGFloat(maxX + 1) * fx + pad)
    let yTop = max(0, CGFloat(minY) * fy - pad)
    let yBot = min(m.extent.height, CGFloat(maxY + 1) * fy + pad)
    return CGRect(x: x0, y: m.extent.height - yBot, width: x1 - x0, height: yBot - yTop)
}

let final = crop ? out.cropped(to: contentBBox(mask)) : out
guard let cg = ctx.createCGImage(final, from: final.extent) else { die("Errore render", 4) }
let rep = NSBitmapImageRep(cgImage: cg)
guard let png = rep.representation(using: .png, properties: [:]) else { die("Errore PNG", 4) }
try png.write(to: URL(fileURLWithPath: outputPath))
print("OK -> \(outputPath) (\(cg.width)x\(cg.height)) erode=\(Int(r))px")
