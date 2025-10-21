"use client"

import { useState, ChangeEvent, FormEvent } from "react"
import { useRouter } from "next/navigation"

import { Navbar } from "@/components/navbar"
import { Footer } from "@/components/footer"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Button } from "@/components/ui/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

export default function DoarPage() {
  const router = useRouter()
  const [formData, setFormData] = useState({
    nome: "",
    data_nascimento: "",
    castrado: "",
    vale_castracao: "",
    sexo: "",
    descricao: "",
    imagens: [] as File[],
  })
  const [preview, setPreview] = useState<string[]>([])
  const [loading, setLoading] = useState(false)

  const handleChange = (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value })
  }

  const handleSelect = (key: string, value: string) => {
    setFormData({ ...formData, [key]: value })
  }

  const handleFileChange = (e: ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files ? Array.from(e.target.files) : []
    setFormData({ ...formData, imagens: files })
    setPreview(files.map((file) => URL.createObjectURL(file)))
  }

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault()
    setLoading(true)

    try {
      const data = new FormData()
      data.append("nome", formData.nome)
      data.append("descricao", formData.descricao)
      data.append("sexo", formData.sexo)
      data.append("castrado", formData.castrado)
      data.append("vale_castracao", formData.vale_castracao)

      if (formData.data_nascimento)
        data.append("data_nascimento", formData.data_nascimento)

      formData.imagens.forEach((img) => data.append("imagens[]", img))

      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/animais`, {
        method: "POST",
        body: data,
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token") || ""}`,
        },
      })

      if (!res.ok) throw new Error("Falha ao criar o animal")

      router.push("/adotar")
    } catch (error) {
      console.error("Erro ao criar anúncio:", error)
      alert("Não foi possível criar o anúncio. Tente novamente.")
    } finally {
      setLoading(false)
    }
  }

  return (
    <>
      <Navbar />
      <main className="min-h-screen pt-24 pb-16 bg-muted/30">
        <div className="max-w-2xl mx-auto px-4">
          <Card>
            <CardHeader>
              <CardTitle className="text-2xl text-center font-bold">
                Formulário de Anúncio para Adoção
              </CardTitle>
            </CardHeader>

            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-6">
                {/* Nome */}
                <div className="space-y-2">
                  <Label htmlFor="nome">Nome</Label>
                  <Input id="nome" name="nome" value={formData.nome} onChange={handleChange} required />
                </div>

                {/* Linha: Data de nascimento / Castrado / Vale / Sexo */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="data_nascimento">Data de Nascimento</Label>
                    <Input
                      id="data_nascimento"
                      name="data_nascimento"
                      type="date"
                      value={formData.data_nascimento}
                      onChange={handleChange}
                      max={new Date().toISOString().split("T")[0]} // impede datas futuras
                    />
                  </div>

                  <div className="space-y-2">
                    <Label>Castrado?</Label>
                    <Select onValueChange={(v) => handleSelect("castrado", v)}>
                      <SelectTrigger><SelectValue placeholder="Selecione" /></SelectTrigger>
                      <SelectContent>
                        <SelectItem value="1">Sim</SelectItem>
                        <SelectItem value="0">Não</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div className="space-y-2">
                    <Label>Vale-Castração?</Label>
                    <Select onValueChange={(v) => handleSelect("vale_castracao", v)}>
                      <SelectTrigger><SelectValue placeholder="Selecione" /></SelectTrigger>
                      <SelectContent>
                        <SelectItem value="1">Sim</SelectItem>
                        <SelectItem value="0">Não</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div className="space-y-2">
                    <Label>Sexo</Label>
                    <Select onValueChange={(v) => handleSelect("sexo", v)}>
                      <SelectTrigger><SelectValue placeholder="Selecione" /></SelectTrigger>
                      <SelectContent>
                        <SelectItem value="macho">Macho</SelectItem>
                        <SelectItem value="femea">Fêmea</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                {/* Descrição */}
                <div className="space-y-2">
                  <Label htmlFor="descricao">Descrição do Animal</Label>
                  <Textarea
                    id="descricao"
                    name="descricao"
                    placeholder="Fale sobre o comportamento e personalidade do animal..."
                    rows={4}
                    value={formData.descricao}
                    onChange={handleChange}
                  />
                </div>

                {/* Upload de Imagens */}
                <div className="space-y-3">
                  <Label>Coloque imagens do Animal</Label>
                  <div className="border border-dashed rounded-md border-muted-foreground/50 p-6 text-center hover:bg-muted/40 transition">
                    <Input
                      id="imagens"
                      type="file"
                      accept="image/*"
                      multiple
                      onChange={handleFileChange}
                      className="hidden"
                    />
                    <label
                      htmlFor="imagens"
                      className="cursor-pointer text-sm text-muted-foreground"
                    >
                      Clique ou arraste para enviar imagens
                    </label>
                    {preview.length > 0 && (
                      <div className="grid grid-cols-3 gap-2 mt-4">
                        {preview.map((src, index) => (
                          <img
                            key={index}
                            src={src}
                            alt={`Prévia ${index + 1}`}
                            className="h-24 w-full object-cover rounded-md border"
                          />
                        ))}
                      </div>
                    )}
                  </div>
                </div>

                {/* Botão */}
                <Button type="submit" className="w-full" disabled={loading}>
                  {loading ? "Enviando..." : "Salvar e Ir para Adoção"}
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>
      </main>
      <Footer />
    </>
  )
}
