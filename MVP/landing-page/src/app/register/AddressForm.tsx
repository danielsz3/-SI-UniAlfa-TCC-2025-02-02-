"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Label } from "@/components/ui/label"
import { Input } from "@/components/ui/input"

export default function AddressForm({ onNext, onBack, defaultValues = {} }: any) {
  const [form, setForm] = useState({
    cep: defaultValues.cep || "",
    logradouro: defaultValues.logradouro || "",
    complemento: defaultValues.complemento || "",
    numero: defaultValues.numero || "",
    cidade: defaultValues.cidade || "",
    estado: defaultValues.estado || "",
  })
  const [loading, setLoading] = useState(false)
  const [erroCep, setErroCep] = useState("")

  function handleChange(e: React.ChangeEvent<HTMLInputElement>) {
    setForm({ ...form, [e.target.name]: e.target.value })
  }

  async function handleCepBlur() {
    const cep = form.cep.replace(/\D/g, "")
    if (cep.length !== 8) {
      setErroCep("CEP inválido")
      return
    }
    setErroCep("")
    setLoading(true)
    try {
      const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
      const data = await res.json()
      if (data.erro) {
        setErroCep("CEP não encontrado")
      } else {
        setForm(f => ({
          ...f,
          logradouro: data.logradouro || "",
          cidade: data.localidade || "",
          estado: data.uf || "",
          complemento: data.complemento || f.complemento,
        }))
      }
    } catch {
      setErroCep("Erro ao buscar CEP")
    } finally {
      setLoading(false)
    }
  }

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    onNext(form)
  }

  return (
    <form className="space-y-6 w-full max-w-md mx-auto bg-white dark:bg-slate-800 rounded-lg shadow-lg p-8 mt-8" onSubmit={handleSubmit}>
      <h2 className="text-xl font-bold mb-4 text-slate-900 dark:text-white">Endereço</h2>
      <div className="grid gap-4">
        <div className="grid gap-2">
          <Label htmlFor="cep">CEP</Label>
          <Input
            id="cep"
            name="cep"
            required
            placeholder="CEP"
            value={form.cep}
            onChange={handleChange}
            onBlur={handleCepBlur}
            maxLength={9}
            disabled={loading}
          />
          {erroCep && <span className="text-red-500 text-xs">{erroCep}</span>}
        </div>
        <div className="grid gap-2">
          <Label htmlFor="logradouro">Logradouro</Label>
          <Input
            id="logradouro"
            name="logradouro"
            required
            placeholder="Logradouro"
            value={form.logradouro}
            onChange={handleChange}
          />
        </div>
        <div className="grid gap-2">
          <Label htmlFor="complemento">Complemento</Label>
          <Input
            id="complemento"
            name="complemento"
            placeholder="Complemento"
            value={form.complemento}
            onChange={handleChange}
          />
        </div>
        <div className="grid gap-2">
          <Label htmlFor="numero">Número</Label>
          <Input
            id="numero"
            name="numero"
            required
            placeholder="Número"
            value={form.numero}
            onChange={handleChange}
          />
        </div>
        <div className="grid gap-2">
          <Label htmlFor="cidade">Cidade</Label>
          <Input
            id="cidade"
            name="cidade"
            required
            placeholder="Cidade"
            value={form.cidade}
            onChange={handleChange}
          />
        </div>
        <div className="grid gap-2">
          <Label htmlFor="estado">Estado</Label>
          <Input
            id="estado"
            name="estado"
            required
            placeholder="Estado"
            value={form.estado}
            onChange={handleChange}
          />
        </div>
      </div>
      <div className="flex gap-2">
        <Button type="button" variant="outline" onClick={onBack} className="w-1/2">Voltar</Button>
        <Button type="submit" className="w-1/2" disabled={loading}>Próximo</Button>
      </div>
    </form>
  )
}