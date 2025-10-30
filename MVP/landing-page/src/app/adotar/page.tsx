"use client"

import { useCallback, useEffect, useRef, useState } from "react"
import Link from "next/link"
import { Navbar } from "@/components/navbar"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

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

type AgeRangeKey = "any" | "0_1" | "1_3" | "3_8" | "8_plus"

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
  const imagemUrl = animal.imagens?.[0]?.caminho ? `${storageUrl}/${animal.imagens[0].caminho}` : null

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
          <div className="flex items-center justify-center h-full text-muted-foreground">Sem imagem</div>
        )}
        <div className="absolute top-2 right-2 flex gap-1">
          {animal.tamanho && <Badge variant="secondary" className="capitalize">{animal.tamanho}</Badge>}
        </div>
      </div>

      <CardHeader className="pb-3">
        <div className="flex items-start justify-between gap-2">
          <CardTitle className="text-xl">{animal.nome}</CardTitle>
          <Badge variant="outline" className="capitalize shrink-0">{animal.sexo}</Badge>
        </div>
        <CardDescription className="capitalize">{animal.tipo_animal}</CardDescription>
      </CardHeader>

      <CardContent className="pb-3 space-y-1 text-sm text-muted-foreground">
        <p>{calcularIdade(animal.data_nascimento)}</p>
        {animal.nivel_energia && <p className="capitalize">Energia: {animal.nivel_energia}</p>}
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

/** converte chave de faixa etária em intervalo de nascimento (YYYY-MM-DD) */
function ageRangeToBirthdateRange(key: AgeRangeKey) {
  const hoje = new Date()
  const isoDate = (d: Date) => d.toISOString().split("T")[0]
  switch (key) {
    case "0_1": {
      const from = new Date(hoje); from.setFullYear(from.getFullYear() - 1)
      return { from: isoDate(from), to: isoDate(hoje) }
    }
    case "1_3": {
      const from = new Date(hoje); from.setFullYear(from.getFullYear() - 3)
      const to = new Date(hoje); to.setFullYear(to.getFullYear() - 1)
      return { from: isoDate(from), to: isoDate(to) }
    }
    case "3_8": {
      const from = new Date(hoje); from.setFullYear(from.getFullYear() - 8)
      const to = new Date(hoje); to.setFullYear(to.getFullYear() - 3)
      return { from: isoDate(from), to: isoDate(to) }
    }
    case "8_plus": {
      const from = new Date(hoje); from.setFullYear(from.getFullYear() - 100)
      const to = new Date(hoje); to.setFullYear(to.getFullYear() - 8)
      return { from: isoDate(from), to: isoDate(to) }
    }
    default:
      return {}
  }
}

export default function AdotarPageClient() {
  const [animais, setAnimais] = useState<Animal[]>([])
  const [loading, setLoading] = useState(false)
  const [backgroundLoading, setBackgroundLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  // Paginação / estado incremental
  const [hasMore, setHasMore] = useState(true)
  const [currentPage, setCurrentPage] = useState<number | null>(1)
  const nextUrlRef = useRef<string | null>(null)
  const paginationModeRef = useRef<"none" | "links" | "pages">("none")
  const perPage = 12
  const safetyMaxPages = 500

  // filtros
  const [tipoAnimal, setTipoAnimal] = useState<string>("all")
  const [sexo, setSexo] = useState<string>("all")
  const [ageRange, setAgeRange] = useState<AgeRangeKey>("any")

  const sentinelRef = useRef<HTMLDivElement | null>(null)
  const observerRef = useRef<IntersectionObserver | null>(null)
  const apiUrl = process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000/api"

  // Cancel flag para abortar background fetch se filtros mudarem / componente desmontar
  const abortedRef = useRef(false)

  const buildBaseUrl = useCallback(() => {
    const params: Record<string, string> = { situacao: "disponivel" }
    if (tipoAnimal && tipoAnimal !== "all") params.tipo_animal = tipoAnimal
    if (sexo && sexo !== "all") params.sexo = sexo
    if (ageRange !== "any") {
      const { from, to } = ageRangeToBirthdateRange(ageRange)
      if (from) params.data_nascimento_from = from
      if (to) params.data_nascimento_to = to
    }
    const q = new URLSearchParams(params).toString()
    return `${apiUrl}/animais?${q}`
  }, [apiUrl, tipoAnimal, sexo, ageRange])

  const buildPageUrl = useCallback((page: number) => {
    const params: Record<string, string> = {
      situacao: "disponivel",
      page: String(page),
      limit: String(perPage),
    }
    if (tipoAnimal && tipoAnimal !== "all") params.tipo_animal = tipoAnimal
    if (sexo && sexo !== "all") params.sexo = sexo
    if (ageRange !== "any") {
      const { from, to } = ageRangeToBirthdateRange(ageRange)
      if (from) params.data_nascimento_from = from
      if (to) params.data_nascimento_to = to
    }
    const q = new URLSearchParams(params).toString()
    return `${apiUrl}/animais?${q}`
  }, [apiUrl, tipoAnimal, sexo, ageRange])

  // parseResponse é puro: retorna items + meta info, sem side-effects
  const parseResponse = useCallback(async (res: Response) => {
    if (!res.ok) {
      const text = await res.text()
      throw new Error(text || `HTTP ${res.status}`)
    }
    const json = await res.json()
    // array simples = API retornou todos
    if (Array.isArray(json)) {
      return { items: json as Animal[], mode: "none" as const, nextUrl: null, currentPage: null, lastPage: null }
    }
    // paginator estilo Laravel
    if (Array.isArray(json.data)) {
      const items = json.data as Animal[]
      const nextUrl = json.links?.next || json.meta?.next_page_url || null
      const current = json.meta?.current_page ?? null
      const last = json.meta?.last_page ?? null
      const mode = nextUrl ? "links" as const : "pages" as const
      return { items, mode, nextUrl, currentPage: current, lastPage: last }
    }
    // fallback
    const items = Array.isArray(json.data) ? json.data : []
    return { items: items as Animal[], mode: "none" as const, nextUrl: null, currentPage: null, lastPage: null }
  }, [])

  // Fetch inicial + background fetching das páginas restantes quando necessário
  const loadInitial = useCallback(async () => {
    abortedRef.current = false
    setLoading(true)
    setBackgroundLoading(false)
    setError(null)
    setHasMore(true)
    nextUrlRef.current = null
    paginationModeRef.current = "none"
    setCurrentPage(1)

    try {
      const url = buildBaseUrl()
      const res = await fetch(url, { cache: "no-store", headers: { Accept: "application/json" } })
      const parsed = await parseResponse(res)

      setAnimais(parsed.items || [])

      // Atualiza refs/estados com o modo detectado
      paginationModeRef.current = parsed.mode
      nextUrlRef.current = parsed.nextUrl ?? null

      if (parsed.mode === "links") {
        setHasMore(!!parsed.nextUrl)
        if (parsed.currentPage) setCurrentPage(parsed.currentPage)
        // background fetch seguindo links.next
        void (async () => {
          if (abortedRef.current) return
          setBackgroundLoading(true)
          let pagesFetched = 0
          try {
            while (nextUrlRef.current && !abortedRef.current && pagesFetched < safetyMaxPages) {
              const nxt = nextUrlRef.current as string
              const r = await fetch(nxt, { cache: "no-store", headers: { Accept: "application/json" } })
              const p = await parseResponse(r)
              if (abortedRef.current) break
              if (p.items && p.items.length > 0) {
                setAnimais((prev) => [...prev, ...p.items])
              }
              // atualizar nextUrlRef a partir do p.nextUrl
              nextUrlRef.current = p.nextUrl ?? null
              pagesFetched += 1
            }
          } catch (e) {
            console.error("Erro no background fetch (links):", e)
          } finally {
            if (!abortedRef.current) {
              setBackgroundLoading(false)
              setHasMore(false)
            }
          }
        })()
      } else if (parsed.mode === "pages") {
        const cur = parsed.currentPage ?? 1
        const last = parsed.lastPage ?? null
        setCurrentPage(cur)
        if (last && last > cur) {
          setBackgroundLoading(true)
          void (async () => {
            try {
              for (let p = cur + 1, pagesFetched = 0; p <= last && !abortedRef.current && pagesFetched < safetyMaxPages; p++, pagesFetched++) {
                const pageUrl = buildPageUrl(p)
                const r = await fetch(pageUrl, { cache: "no-store", headers: { Accept: "application/json" } })
                const parsedPage = await parseResponse(r)
                if (abortedRef.current) break
                if (parsedPage.items && parsedPage.items.length > 0) {
                  setAnimais((prev) => [...prev, ...parsedPage.items])
                }
                setCurrentPage(p)
              }
            } catch (e) {
              console.error("Erro no background fetch (pages):", e)
            } finally {
              if (!abortedRef.current) {
                setBackgroundLoading(false)
                setHasMore(false)
              }
            }
          })()
        } else {
          setHasMore(false)
        }
      } else {
        // array completo
        setHasMore(false)
      }
    } catch (err: any) {
      console.error("Erro ao carregar animais:", err)
      setError(err.message || "Erro ao carregar animais")
      setAnimais([])
      setHasMore(false)
      setBackgroundLoading(false)
    } finally {
      setLoading(false)
    }
  }, [buildBaseUrl, buildPageUrl, parseResponse])

  // Carregar a próxima página manual (fallback)
  const loadNext = useCallback(async () => {
    if (!hasMore || loading) return
    setLoading(true)
    setError(null)

    try {
      if (paginationModeRef.current === "links" && nextUrlRef.current) {
        const r = await fetch(nextUrlRef.current, { cache: "no-store", headers: { Accept: "application/json" } })
        const parsed = await parseResponse(r)
        setAnimais((prev) => [...prev, ...(parsed.items || [])])
        // atualiza ref/estado
        paginationModeRef.current = parsed.mode
        nextUrlRef.current = parsed.nextUrl ?? null
        setHasMore(!!nextUrlRef.current)
        if (parsed.currentPage) setCurrentPage(parsed.currentPage)
        return
      }

      // modo 'pages' ou fallback: requisitar page=current+1
      const nextPage = (currentPage ?? 1) + 1
      const pageUrl = buildPageUrl(nextPage)
      const r = await fetch(pageUrl, { cache: "no-store", headers: { Accept: "application/json" } })
      const parsed = await parseResponse(r)
      setAnimais((prev) => [...prev, ...(parsed.items || [])])

      // atualizar modo/refs
      paginationModeRef.current = parsed.mode
      nextUrlRef.current = parsed.nextUrl ?? null

      if (parsed.lastPage !== undefined && parsed.currentPage !== undefined) {
        const cur = parsed.currentPage ?? nextPage
        const last = parsed.lastPage ?? cur
        setCurrentPage(cur)
        setHasMore(cur < last)
      } else {
        setCurrentPage(nextPage)
        setHasMore(((parsed.items || []) as Animal[]).length === perPage)
      }
    } catch (err: any) {
      console.error("Erro ao carregar próxima página:", err)
      setError(err.message || "Erro ao carregar mais animais")
      setHasMore(false)
    } finally {
      setLoading(false)
    }
  }, [buildPageUrl, currentPage, hasMore, loading, parseResponse])

  // chama loadInitial quando filtros mudarem; cancela background fetch anterior
  useEffect(() => {
    abortedRef.current = false
    loadInitial()
    return () => {
      abortedRef.current = true
    }
  }, [tipoAnimal, sexo, ageRange, loadInitial])

  // IntersectionObserver para lazy-load (fallback)
  useEffect(() => {
    if (!sentinelRef.current) return
    if (observerRef.current) observerRef.current.disconnect()

    observerRef.current = new IntersectionObserver(
      (entries) => {
        const first = entries[0]
        if (first.isIntersecting && hasMore && !loading) {
          loadNext()
        }
      },
      { root: null, rootMargin: "200px", threshold: 0.1 }
    )

    observerRef.current.observe(sentinelRef.current)
    return () => observerRef.current?.disconnect()
  }, [hasMore, loading, loadNext])

  const resetFilters = () => {
    setTipoAnimal("all")
    setSexo("all")
    setAgeRange("any")
  }

  return (
    <>
      <Navbar />
      <main className="min-h-screen pt-24 pb-16">
        <div className="container mx-auto px-4">
          <div className="text-center mb-6">
            <h1 className="text-4xl font-bold mb-2">Animais Para Adoção</h1>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
              Encontre seu novo melhor amigo. Filtre por tipo, gênero e idade.
            </p>
          </div>

          {/* filtros */}
          <div className="mb-6 flex flex-col md:flex-row gap-3 items-start md:items-end justify-between">
            <div className="flex gap-3 w-full md:w-auto">
              <div>
                <label className="block text-sm mb-1">Tipo</label>
                <Select onValueChange={(v) => setTipoAnimal(v)} value={tipoAnimal}>
                  <SelectTrigger className="w-40"><SelectValue placeholder="Todos" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">Todos</SelectItem>
                    <SelectItem value="cao">Cão</SelectItem>
                    <SelectItem value="gato">Gato</SelectItem>
                    <SelectItem value="outro">Outro</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <label className="block text-sm mb-1">Gênero</label>
                <Select onValueChange={(v) => setSexo(v)} value={sexo}>
                  <SelectTrigger className="w-40"><SelectValue placeholder="Todos" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">Todos</SelectItem>
                    <SelectItem value="macho">Macho</SelectItem>
                    <SelectItem value="femea">Fêmea</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <label className="block text-sm mb-1">Idade</label>
                <Select onValueChange={(v) => setAgeRange(v as AgeRangeKey)} value={ageRange}>
                  <SelectTrigger className="w-40"><SelectValue placeholder="Qualquer" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="any">Qualquer</SelectItem>
                    <SelectItem value="0_1">Até 1 ano</SelectItem>
                    <SelectItem value="1_3">1 - 3 anos</SelectItem>
                    <SelectItem value="3_8">3 - 8 anos</SelectItem>
                    <SelectItem value="8_plus">8+ anos</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="flex gap-2">
              <Button variant="outline" onClick={resetFilters}>Limpar</Button>
              <Button onClick={() => loadInitial()}>Aplicar</Button>
            </div>
          </div>

          {/* lista */}
          {animais.length === 0 && !loading ? (
            <div className="text-center py-16">
              <p className="text-muted-foreground text-lg mb-4">Nenhum animal disponível para adoção no momento.</p>
              <Button asChild><Link href="/doar-pet">Cadastrar Animal</Link></Button>
            </div>
          ) : (
            <>
              <section className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {animais.map((animal) => (
                  <AnimalCard key={animal.id} animal={animal} />
                ))}
              </section>

              <div ref={sentinelRef} />

              <div className="mt-6 flex flex-col items-center gap-3">
                {loading && <p className="text-sm text-muted-foreground">Carregando...</p>}
                {backgroundLoading && <p className="text-sm text-muted-foreground">Carregando o restante em background...</p>}
                {error && <p className="text-sm text-destructive">{error}</p>}
                {!loading && !backgroundLoading && hasMore && (
                  <Button onClick={() => loadNext()}>Carregar mais</Button>
                )}
                {!hasMore && animais.length > 0 && (
                  <p className="text-sm text-muted-foreground">Todos os animais carregados.</p>
                )}
              </div>
            </>
          )}
        </div>
      </main>
    </>
  )
}
