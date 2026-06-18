// removebg.swift — Rimozione sfondo on-device (Vision, macOS 14+)
// Uso:  swift removebg.swift <input> <output.png> [--crop]
//   --crop  ritaglia stretto attorno al soggetto (utile per "prendere un pezzo")
// Produce un PNG con sfondo trasparente.

import Foundation
import Vision
import CoreImage
import AppKit

let args = CommandLine.arguments
guard args.count >= 3 else {
    FileHandle.standardError.write("Uso: swift removebg.swift <input> <output.png> [--crop]\n".data(using: .utf8)!)
    exit(1)
}
let inputPath = args[1]
let outputPath = args[2]
let crop = args.contains("--crop")

guard let inputImage = CIImage(contentsOf: URL(fileURLWithPath: inputPath),
                               options: [.applyOrientationProperty: true]) else {
    FileHandle.standardError.write("Impossibile leggere \(inputPath)\n".data(using: .utf8)!)
    exit(1)
}

let handler = VNImageRequestHandler(ciImage: inputImage, options: [:])
let request = VNGenerateForegroundInstanceMaskRequest()

do {
    try handler.perform([request])
    guard let result = request.results?.first else {
        FileHandle.standardError.write("Nessun soggetto rilevato in \(inputPath)\n".data(using: .utf8)!)
        exit(2)
    }
    let masked = try result.generateMaskedImage(
        ofInstances: result.allInstances,
        from: handler,
        croppedToInstancesExtent: crop)

    let ciOutput = CIImage(cvPixelBuffer: masked)
    let context = CIContext()
    guard let cg = context.createCGImage(ciOutput, from: ciOutput.extent) else {
        FileHandle.standardError.write("Errore creazione immagine\n".data(using: .utf8)!)
        exit(3)
    }
    let rep = NSBitmapImageRep(cgImage: cg)
    guard let png = rep.representation(using: .png, properties: [:]) else {
        FileHandle.standardError.write("Errore encoding PNG\n".data(using: .utf8)!)
        exit(4)
    }
    try png.write(to: URL(fileURLWithPath: outputPath))
    print("OK -> \(outputPath)  (\(cg.width)x\(cg.height))")
} catch {
    FileHandle.standardError.write("Errore Vision: \(error)\n".data(using: .utf8)!)
    exit(5)
}
