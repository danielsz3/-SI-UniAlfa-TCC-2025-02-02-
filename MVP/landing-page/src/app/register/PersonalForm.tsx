"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Label } from "@/components/ui/label"
import { Input } from "@/components/ui/input"

export default function PersonalForm({ onNext, defaultValues = {} }: any) {
  const [form, setForm] = useState({
    nome: defaultValues.nome || "",
    telefone: defaultValues.telefone || "",
    email: defaultValues.email || "",
    senha: "",
    confirmarSenha: "",
  })
  const [erro, setErro] = useState("")

  function handleChange(e: React.ChangeEvent<HTMLInputElement>) {
    setForm({ ...form, [e.target.name]: e.target.value })
  }

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    if (form.senha !== form.confirmarSenha) {
      setErro("As senhas não coincidem.")
      return
    }
    setErro("")
    onNext(form)
  }

  return (
    <form
      className="space-y-6 w-full max-w-md mx-auto bg-white dark:bg-slate-800 rounded-lg shadow-lg p-8 mt-8"
      onSubmit={handleSubmit}
    >
      <h2 className="text-xl font-bold mb-4 text-slate-900 dark:text-white">Dados Pessoais</h2>
      <div className="grid gap-4">
        <div className="grid gap-2">
          <Label htmlFor="nome">Nome</Label>
          <Input id="nome" name="nome" required minLength={3} placeholder="Nome" value={form.nome} onChange={handleChange} />
        </div>
        <div className="grid gap-2">
          <Label htmlFor="telefone">Telefone</Label>
          <Input id="telefone" name="telefone" required placeholder="Telefone" value={form.telefone} onChange={handleChange} />
        </div>
        <div className="grid gap-2">
          <Label htmlFor="email">E-mail</Label>
          <Input id="email" name="email" type="email" required placeholder="E-mail" value={form.email} onChange={handleChange} />
        </div>
        <div className="grid gap-2">
          <Label htmlFor="senha">Senha</Label>
          <Input id="senha" name="senha" type="password" required minLength={6} placeholder="Senha" value={form.senha} onChange={handleChange} />
        </div>
        <div className="grid gap-2">
          <Label htmlFor="confirmarSenha">Confirmar Senha</Label>
          <Input id="confirmarSenha" name="confirmarSenha" type="password" required minLength={6} placeholder="Confirmar Senha" value={form.confirmarSenha} onChange={handleChange} />
        </div>
        {erro && <span className="text-red-500 text-xs">{erro}</span>}
      </div>
      <Button type="submit" className="w-full mt-2">Próximo</Button>
    </form>
  )
}
