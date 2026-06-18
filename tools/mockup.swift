// mockup.swift <cutout.png|webp> <output.png>
// Compone il cutout sullo sfondo reale del sito (gradiente nero -> grigio-900)
// per verificare la leggibilità del prodotto sul fondo scuro.
import Foundation
import CoreImage
import AppKit

let args = CommandLine.arguments
guard args.count >= 3 else { FileHandle.standardError.write("Uso: mockup.swift <in> <out.png>\n".data(using:.utf8)!); exit(1) }
guard let fg = CIImage(contentsOf: URL(fileURLWithPath: args[1])) else { exit(1) }

let W: CGFloat = 900, H: CGFloat = 950

// Sfondo: gradiente verticale nero (basso) -> grigio scuro (alto), come bg-gradient-to-b from-black to-gray-900
let grad = CIFilter(name: "CILinearGradient")!
grad.setValue(CIVector(x: 0, y: 0), forKey: "inputPoint0")        // basso = nero
grad.setValue(CIVector(x: 0, y: H), forKey: "inputPoint1")        // alto = grigio
grad.setValue(CIColor(red: 0, green: 0, blue: 0), forKey: "inputColor0")
grad.setValue(CIColor(red: 17/255, green: 24/255, blue: 39/255), forKey: "inputColor1") // gray-900
let bg = grad.outputImage!.cropped(to: CGRect(x: 0, y: 0, width: W, height: H))

// Scala il prodotto a "contain" con un po' di margine
let ext = fg.extent
let scale = min(W/ext.width, H/ext.height) * 0.82
let scaled = fg.transformed(by: CGAffineTransform(scaleX: scale, y: scale))
let se = scaled.extent
let tx = (W - se.width)/2 - se.minX
let ty = (H - se.height)/2 - se.minY
let placed = scaled.transformed(by: CGAffineTransform(translationX: tx, y: ty))

let composite = placed.composited(over: bg).cropped(to: CGRect(x: 0, y: 0, width: W, height: H))
let ctx = CIContext()
let cg = ctx.createCGImage(composite, from: composite.extent)!
let rep = NSBitmapImageRep(cgImage: cg)
try! rep.representation(using: .png, properties: [:])!.write(to: URL(fileURLWithPath: args[2]))
print("OK -> \(args[2])")
