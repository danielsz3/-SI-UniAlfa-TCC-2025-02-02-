"use client"

import { useEffect, useState, useCallback } from "react"
import { ChevronLeft, ChevronRight } from "lucide-react"

interface ImageCarouselProps {
  images: string[]
  alt?: string
}

export function ImageCarousel({ images, alt = "imagem" }: ImageCarouselProps) {
  const [index, setIndex] = useState(0)

  useEffect(() => {
    if (index >= images.length) setIndex(0)
  }, [images.length, index])

  const prev = useCallback(() => {
    setIndex((i) => (i - 1 + images.length) % Math.max(images.length, 1))
  }, [images.length])

  const next = useCallback(() => {
    setIndex((i) => (i + 1) % Math.max(images.length, 1))
  }, [images.length])

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "ArrowLeft") prev()
      if (e.key === "ArrowRight") next()
    }
    window.addEventListener("keydown", onKey)
    return () => window.removeEventListener("keydown", onKey)
  }, [prev, next])

  if (!images || images.length === 0) {
    return (
      <div className="aspect-square bg-muted rounded-lg flex items-center justify-center text-muted-foreground">
        Sem imagem
      </div>
    )
  }

  return (
    <div className="space-y-3">
      <div className="relative aspect-square bg-muted rounded-lg overflow-hidden">
        {/* Imagem principal */}
        <img
          src={images[index]}
          alt={`${alt} ${index + 1}`}
          className="w-full h-full object-cover"
        />

        {/* Navegação */}
        {images.length > 1 && (
          <>
            <button
              type="button"
              onClick={prev}
              aria-label="Anterior"
              className="absolute left-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center rounded-full bg-background/70 hover:bg-background p-2 shadow-md"
            >
              <ChevronLeft className="h-5 w-5 text-foreground" />
            </button>

            <button
              type="button"
              onClick={next}
              aria-label="Próximo"
              className="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center rounded-full bg-background/70 hover:bg-background p-2 shadow-md"
            >
              <ChevronRight className="h-5 w-5 text-foreground" />
            </button>

            {/* indicador no canto inferior */}
            <div className="absolute left-1/2 -translate-x-1/2 bottom-2 px-3 py-1 bg-background/60 rounded-full text-sm text-muted-foreground shadow-sm">
              {index + 1} / {images.length}
            </div>
          </>
        )}
      </div>

      {/* Miniaturas */}
      {images.length > 1 && (
        <div className="flex gap-2 overflow-x-auto">
          {images.map((src, i) => (
            <button
              key={i}
              type="button"
              onClick={() => setIndex(i)}
              className={`flex-shrink-0 rounded-md overflow-hidden border ${
                i === index ? "ring-2 ring-primary" : "border-border"
              }`}
              aria-current={i === index}
              aria-label={`Ver imagem ${i + 1}`}
            >
              <img src={src} alt={`${alt} miniatura ${i + 1}`} className="w-20 h-20 object-cover" />
            </button>
          ))}
        </div>
      )}
    </div>
  )
}
