import Link from "next/link"
import { Navbar } from "@/components/navbar"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"

interface Animal {
  id: number
  nome: string
  sexo: string
  tipo_animal: string
  tamanho?: string
  nivel_energia?: string
  data_nascimento?: string
  imagens?: Array<{ caminho: string }>
  created_at: string
}

async function fetchAnimais(): Promise<Animal[]> {
  try {
    // ✅ Fallback para evitar undefined
    const apiUrl = process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000/api"
    const url = `${apiUrl}/animais?limit=12`
    
    console.log("🔗 Fetching from:", url)
    
    const res = await fetch(url, {
      cache: "no-store",
      headers: {
        "Accept": "application/json",
      }
    })
    
    console.log("📡 Status:", res.status)
    
    if (!res.ok) {
      const errorText = await res.text()
      console.error("❌ Error response:", errorText)
      throw new Error(`API Error: ${res.status}`)
    }
    
    const json = await res.json()
    console.log("✅ Response structure:", json)
    
    // ✅ Ajustar baseado na estrutura da API
    const animais = Array.isArray(json) ? json : (json.data || [])
    console.log(`✅ Total animais: ${animais.length}`)
    
    return animais
  } catch (error) {
    console.error("💥 Fetch error:", error)
    return []
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

function AnimalCard({ animal }: { animal: Animal }) {
  const storageUrl = process.env.NEXT_PUBLIC_STORAGE_URL || "http://127.0.0.1:8000/storage"
  const imagemUrl = animal.imagens?.[0]?.caminho 
    ? `${storageUrl}/${animal.imagens[0].caminho}`
    : null

  return (
    <Card className="group overflow-hidden hover:shadow-lg transition-shadow">
      <div className="relative h-48 bg-muted overflow-hidden">
        {imagemUrl ? (
          <img
            src={imagemUrl}
            alt={animal.nome}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
        ) : (
          <div className="flex items-center justify-center h-full text-muted-foreground">
            Sem imagem
          </div>
        )}
        <div className="absolute top-2 right-2 flex gap-1">
          {animal.tamanho && (
            <Badge variant="secondary" className="capitalize">
              {animal.tamanho}
            </Badge>
          )}
        </div>
      </div>

      <CardHeader className="pb-3">
        <div className="flex items-start justify-between gap-2">
          <CardTitle className="text-xl">{animal.nome}</CardTitle>
          <Badge variant="outline" className="capitalize shrink-0">
            {animal.sexo}
          </Badge>
        </div>
        <CardDescription className="capitalize">
          {animal.tipo_animal}
        </CardDescription>
      </CardHeader>

      <CardContent className="pb-3 space-y-1 text-sm text-muted-foreground">
        <p>{calcularIdade(animal.data_nascimento)}</p>
        {animal.nivel_energia && (
          <p className="capitalize">Energia: {animal.nivel_energia}</p>
        )}
      </CardContent>

      <CardFooter className="flex-col gap-2">
        <Button asChild className="w-full">
          <Link href={`/adotar/${animal.id}`}>Ver Detalhes</Link>
        </Button>
        <p className="text-xs text-muted-foreground text-center">
          Cadastrado em {new Date(animal.created_at).toLocaleDateString("pt-BR")}
        </p>
      </CardFooter>
    </Card>
  )
}

export default async function AdotarPage() {
  const animais = await fetchAnimais()
  
  console.log(`🐾 Renderizando ${animais.length} animais`)

  return (
    <>
      <Navbar />
      <main className="min-h-screen pt-24 pb-16">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h1 className="text-4xl font-bold mb-4">Animais Para Adoção</h1>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
              Encontre seu novo melhor amigo. Todos os nossos pets estão esperando por um lar cheio de amor.
            </p>
          </div>

          {animais.length === 0 ? (
            <div className="text-center py-16">
              <p className="text-muted-foreground text-lg mb-4">
                Nenhum animal disponível para adoção no momento.
              </p>
              <Button asChild>
                <Link href="/doar-pet">Cadastrar Animal</Link>
              </Button>
            </div>
          ) : (
            <section className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              {animais.map((animal) => (
                <AnimalCard key={animal.id} animal={animal} />
              ))}
            </section>
          )}
        </div>
      </main>
    </>
  )
}
