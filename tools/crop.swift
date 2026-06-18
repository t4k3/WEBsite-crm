// crop.swift <in> <out.png> <x> <y> <w> <h>   (origine in alto-sinistra, pixel)
import Foundation
import CoreImage
import AppKit

let a = CommandLine.arguments
guard a.count >= 7, let img = CIImage(contentsOf: URL(fileURLWithPath: a[1])) else { exit(1) }
let x = Double(a[3])!, y = Double(a[4])!, w = Double(a[5])!, h = Double(a[6])!
let H = img.extent.height
// converti y dall'alto (immagine) al basso (CoreImage)
let rect = CGRect(x: x, y: H - y - h, width: w, height: h)
let cropped = img.cropped(to: rect)
let ctx = CIContext()
guard let cg = ctx.createCGImage(cropped, from: cropped.extent) else { exit(2) }
let rep = NSBitmapImageRep(cgImage: cg)
try! rep.representation(using: .png, properties: [:])!.write(to: URL(fileURLWithPath: a[2]))
print("OK \(Int(w))x\(Int(h)) -> \(a[2])")
