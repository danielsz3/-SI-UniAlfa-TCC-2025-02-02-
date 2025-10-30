import Link from "next/link"
import { notFound } from "next/navigation"
import { Calendar, Heart, Ruler, Zap } from "lucide-react"

import { Navbar } from "@/components/navbar"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Separator } from "@/components/ui/separator"

import { ImageCarousel } from "@/components/ImageCarousel" // ajuste o caminho se necessário

async function fetchAnimal(id: string) {
  try {
    const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/animais/${id}`, {
      cache: "no-store",
    })
    if (!res.ok) return null
    return await res.json()
  } catch {
    return null
  }
}

function calcularIdade(dataNascimento?: string): string {
  if (!dataNascimento) return "Idade desconhecida"
  const hoje = new Date()
  const nascimento = new Date(dataNascimento)
  const anos = hoje.getFullYear() - nascimento.getFullYear()
  const meses = hoje.getMonth() - nascimento.getMonth()
  
  if (anos === 0) return `${meses} ${meses === 1 ? "mês" : "meses"}`
  return `${anos} ${anos === 1 ? "ano" : "anos"}`
}

export default async function AnimalDetalhesPage({ params }: { params: { id: string } }) {
  const animal = await fetchAnimal(params.id)

  if (!animal) {
    notFound()
  }

  // monta array de URLs completas das imagens
  const imagens: string[] = (animal.imagens || []).map((img: any) =>
    img?.caminho ? `${process.env.NEXT_PUBLIC_STORAGE_URL}/${img.caminho}` : ""
  ).filter(Boolean)

  return (
    <>
      <Navbar />
      <main className="min-h-screen pt-24 pb-16">
        <div className="container mx-auto px-4 max-w-5xl">
          <div className="grid md:grid-cols-2 gap-8">
            {/* Imagem / Carrossel */}
            <div className="space-y-4">
              <ImageCarousel images={imagens} alt={animal.nome} />
            </div>

            {/* Informações */}
            <div className="space-y-6">
              <div>
                <div className="flex items-start justify-between mb-2">
                  <h1 className="text-4xl font-bold">{animal.nome}</h1>
                  <Badge variant="outline" className="capitalize text-base px-3 py-1">
                    {animal.sexo}
                  </Badge>
                </div>
                <p className="text-xl text-muted-foreground capitalize">
                  {animal.tipo_animal}
                </p>
              </div>

              <Separator />

              <div className="grid grid-cols-2 gap-4">
                <Card>
                  <CardContent className="flex items-center gap-3 p-4">
                    <Calendar className="h-5 w-5 text-primary" />
                    <div>
                      <p className="text-sm text-muted-foreground">Idade</p>
                      <p className="font-semibold">{calcularIdade(animal.data_nascimento)}</p>
                    </div>
                  </CardContent>
                </Card>

                {animal.tamanho && (
                  <Card>
                    <CardContent className="flex items-center gap-3 p-4">
                      <Ruler className="h-5 w-5 text-primary" />
                      <div>
                        <p className="text-sm text-muted-foreground">Tamanho</p>
                        <p className="font-semibold capitalize">{animal.tamanho}</p>
                      </div>
                    </CardContent>
                  </Card>
                )}

                {animal.nivel_energia && (
                  <Card>
                    <CardContent className="flex items-center gap-3 p-4">
                      <Zap className="h-5 w-5 text-primary" />
                      <div>
                        <p className="text-sm text-muted-foreground">Energia</p>
                        <p className="font-semibold capitalize">{animal.nivel_energia}</p>
                      </div>
                    </CardContent>
                  </Card>
                )}

                {animal.castrado !== null && (
                  <Card>
                    <CardContent className="flex items-center gap-3 p-4">
                      <Heart className="h-5 w-5 text-primary" />
                      <div>
                        <p className="text-sm text-muted-foreground">Castrado</p>
                        <p className="font-semibold">{animal.castrado ? "Sim" : "Não"}</p>
                      </div>
                    </CardContent>
                  </Card>
                )}
              </div>

              {animal.descricao && (
                <>
                  <Separator />
                  <div>
                    <h2 className="text-xl font-semibold mb-3">Sobre {animal.nome}</h2>
                    <p className="text-muted-foreground leading-relaxed">
                      {animal.descricao}
                    </p>
                  </div>
                </>
              )}

              <Button asChild size="lg" className="w-full">
                <Link href={`/adocao/formulario?animal_id=${animal.id}`}>
                  Quero Adotar {animal.nome}
                </Link>
              </Button>
            </div>
          </div>
        </div>
      </main>
    </>
  )
}
