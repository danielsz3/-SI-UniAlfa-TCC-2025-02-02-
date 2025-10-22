"use client"

import { useState, ChangeEvent, FormEvent } from "react"
import { useRouter } from "next/navigation"

import { Navbar } from "@/components/navbar"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Button } from "@/components/ui/button"
import { FormInput } from "@/components/forms/inputs/FormInput"
import { FormSelect } from "@/components/forms/inputs/FormSelect"
import { ImageUpload } from "@/components/forms/inputs/ImageUpload"

export default function DoarPage() {
  const router = useRouter()
  const [formData, setFormData] = useState({
    nome: "",
    sexo: "",
    data_nascimento: "",
    castrado: "",
    vale_castracao: "",
    tipo_animal: "",
    descricao: "",
    nivel_energia: "",
    tamanho: "",
    tempo_necessario: "",
    ambiente_ideal: "",
    imagens: [] as File[],
  })
  const [preview, setPreview] = useState<string[]>([])
  const [loading, setLoading] = useState(false)

  const handleChange = (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value })
  }

  const handleSelect = (key: string) => (value: string) => {
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
      data.append("sexo", formData.sexo)
      data.append("tipo_animal", formData.tipo_animal)

      if (formData.data_nascimento) data.append("data_nascimento", formData.data_nascimento)
      if (formData.castrado) data.append("castrado", formData.castrado)
      if (formData.vale_castracao) data.append("vale_castracao", formData.vale_castracao)
      if (formData.descricao) data.append("descricao", formData.descricao)
      if (formData.nivel_energia) data.append("nivel_energia", formData.nivel_energia)
      if (formData.tamanho) data.append("tamanho", formData.tamanho)
      if (formData.tempo_necessario) data.append("tempo_necessario", formData.tempo_necessario)
      if (formData.ambiente_ideal) data.append("ambiente_ideal", formData.ambiente_ideal)

      formData.imagens.forEach((img) => data.append("imagens[]", img))

      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/animais`, {
        method: "POST",
        body: data,
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token") || ""}`,
        },
      })

      if (!res.ok) {
        const errorData = await res.json()
        throw new Error(errorData.message || "Falha ao criar o animal")
      }

      router.push("/adotar")
    } catch (error: any) {
      console.error("Erro ao criar anúncio:", error)
      alert(error.message || "Não foi possível criar o anúncio. Tente novamente.")
    } finally {
      setLoading(false)
    }
  }

  return (
    <>
      <Navbar />
      <main className="min-h-screen pt-24 pb-16 bg-muted/30">
        <div className="max-w-3xl mx-auto px-4">
          <Card>
            <CardHeader>
              <CardTitle className="text-2xl text-center font-bold">
                Formulário de Anúncio para Adoção
              </CardTitle>
            </CardHeader>

            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-6">
                <FormInput
                  label="Nome"
                  name="nome"
                  value={formData.nome}
                  onChange={handleChange}
                  required
                  maxLength={100}
                />

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <FormSelect
                    label="Tipo de Animal"
                    required
                    options={[
                      { value: "cao", label: "Cão" },
                      { value: "gato", label: "Gato" },
                      { value: "outro", label: "Outro" },
                    ]}
                    onValueChange={handleSelect("tipo_animal")}
                  />

                  <FormSelect
                    label="Sexo"
                    required
                    options={[
                      { value: "macho", label: "Macho" },
                      { value: "femea", label: "Fêmea" },
                    ]}
                    onValueChange={handleSelect("sexo")}
                  />

                  <FormInput
                    label="Data de Nascimento"
                    name="data_nascimento"
                    type="date"
                    value={formData.data_nascimento}
                    onChange={handleChange}
                    max={new Date().toISOString().split("T")[0]}
                  />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <FormSelect
                    label="Castrado?"
                    options={[
                      { value: "1", label: "Sim" },
                      { value: "0", label: "Não" },
                    ]}
                    onValueChange={handleSelect("castrado")}
                  />

                  <FormSelect
                    label="Vale-Castração?"
                    options={[
                      { value: "1", label: "Sim" },
                      { value: "0", label: "Não" },
                    ]}
                    onValueChange={handleSelect("vale_castracao")}
                  />

                  <FormSelect
                    label="Tamanho"
                    options={[
                      { value: "pequeno", label: "Pequeno" },
                      { value: "medio", label: "Médio" },
                      { value: "grande", label: "Grande" },
                    ]}
                    onValueChange={handleSelect("tamanho")}
                  />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <FormSelect
                    label="Nível de Energia"
                    options={[
                      { value: "baixa", label: "Baixa" },
                      { value: "moderada", label: "Moderada" },
                      { value: "alta", label: "Alta" },
                    ]}
                    onValueChange={handleSelect("nivel_energia")}
                  />

                  <FormSelect
                    label="Tempo Necessário"
                    options={[
                      { value: "pouco_tempo", label: "Pouco Tempo" },
                      { value: "tempo_moderado", label: "Tempo Moderado" },
                      { value: "muito_tempo", label: "Muito Tempo" },
                    ]}
                    onValueChange={handleSelect("tempo_necessario")}
                  />

                  <FormSelect
                    label="Ambiente Ideal"
                    options={[
                      { value: "area_pequena", label: "Área Pequena" },
                      { value: "area_media", label: "Área Média" },
                      { value: "area_externa", label: "Área Externa" },
                    ]}
                    onValueChange={handleSelect("ambiente_ideal")}
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="descricao">Descrição do Animal</Label>
                  <Textarea
                    id="descricao"
                    name="descricao"
                    placeholder="Conte sobre o comportamento, personalidade e história do animal..."
                    rows={4}
                    value={formData.descricao}
                    onChange={handleChange}
                    maxLength={2000}
                  />
                </div>

                <ImageUpload onChange={handleFileChange} preview={preview} />

                <Button type="submit" className="w-full" disabled={loading}>
                  {loading ? "Enviando..." : "Salvar e Ir para Adoção"}
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>
      </main>
    </>
  )
}
