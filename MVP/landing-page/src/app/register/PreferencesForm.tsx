"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Label } from "@/components/ui/label"
import {
  RadioGroup,
  RadioGroupItem,
} from "@/components/ui/radio-group"
import { Navbar } from "@/components/navbar"

export default function PreferencesForm({ onNext, onBack, defaultValues = {} }: any) {
  const [form, setForm] = useState({
    tamanhoPet: defaultValues.tamanhoPet || "",
    tempoCuidar: defaultValues.tempoCuidar || "",
    estiloVida: defaultValues.estiloVida || "",
    espaco: defaultValues.espaco || "",
  })

  function handleChange(name: string, value: string) {
    setForm({ ...form, [name]: value })
  }

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    onNext(form)
  }

  // Helper para aplicar classe de seleção
  function cardClass(selected: boolean) {
    return [
      "border rounded-lg p-4 flex flex-col gap-2 cursor-pointer transition-colors",
      selected
        ? "border-primary bg-primary/10 dark:bg-primary/20 ring-2 ring-primary"
        : "border-muted"
    ].join(" ")
  }

  return (
    <>
      <Navbar />
      <form className="w-full max-w-xl mx-auto bg-white dark:bg-slate-800 rounded-lg shadow-lg p-8 space-y-8 mt-8" onSubmit={handleSubmit}>
        <h2 className="text-2xl font-bold mb-6 text-center text-slate-900 dark:text-white">Preferências</h2>
        <div className="space-y-6">
          {/* 1. Tamanho do pet */}
          <div>
            <Label className="text-base font-semibold">1. Que tamanho de pet você prefere?</Label>
            <p className="text-sm text-muted-foreground mb-2">Considere o espaço da sua casa e sua preferência pessoal.</p>
            <RadioGroup
              name="tamanhoPet"
              value={form.tamanhoPet}
              onValueChange={value => handleChange("tamanhoPet", value)}
              className="grid grid-cols-1 md:grid-cols-3 gap-3"
              required
            >
              <div className={cardClass(form.tamanhoPet === "pequeno")}>
                <label htmlFor="tamanho-pequeno" className="w-full h-full cursor-pointer flex flex-col gap-2">
                  <RadioGroupItem value="pequeno" id="tamanho-pequeno" className="mb-2" />
                  <span className="font-medium">Pequeno</span>
                  <span className="text-xs text-muted-foreground">Pets que cabem no colo, fáceis de carregar (ex: filhotes, raças pequenas)</span>
                </label>
              </div>
              <div className={cardClass(form.tamanhoPet === "medio")}>
                <label htmlFor="tamanho-medio" className="w-full h-full cursor-pointer flex flex-col gap-2">
                  <RadioGroupItem value="medio" id="tamanho-medio" className="mb-2" />
                  <span className="font-medium">Médio</span>
                  <span className="text-xs text-muted-foreground">Pets nem muito grandes nem muito pequenos (até 25kg)</span>
                </label>
              </div>
              <div className={cardClass(form.tamanhoPet === "grande")}>
                <label htmlFor="tamanho-grande" className="w-full h-full cursor-pointer flex flex-col gap-2">
                  <RadioGroupItem value="grande" id="tamanho-grande" className="mb-2" />
                  <span className="font-medium">Grande</span>
                  <span className="text-xs text-muted-foreground">Pets grandes que precisam de mais espaço (acima de 25kg)</span>
                </label>
              </div>
            </RadioGroup>
          </div>

          {/* 2. Tempo hábil para cuidar */}
          <div>
            <Label className="text-base font-semibold">2. Quanto tempo você tem disponível para cuidar do seu pet?</Label>
            <p className="text-sm text-muted-foreground mb-2">Seja honesto sobre sua rotina e disponibilidade diária.</p>
            <RadioGroup
              name="tempoCuidar"
              value={form.tempoCuidar}
              onValueChange={value => handleChange("tempoCuidar", value)}
              className="grid grid-cols-1 md:grid-cols-3 gap-3"
              required
            >
              <div className={cardClass(form.tempoCuidar === "pouco")}>
                <label htmlFor="tempo-pouco" className="w-full h-full cursor-pointer flex flex-col gap-2">
                  <RadioGroupItem value="pouco" id="tempo-pouco" className="mb-2" />
                  <span className="font-medium">Pouco tempo</span>
                  <span className="text-xs text-muted-foreground">Prefere pets mais independentes, rotina corrida</span>
                </label>
              </div>
              <div className={cardClass(form.tempoCuidar === "moderado")}>
                <label htmlFor="tempo-moderado" className="w-full h-full cursor-pointer flex flex-col gap-2">
                  <RadioGroupItem value="moderado" id="tempo-moderado" className="mb-2" />
                  <span className="font-medium">Tempo moderado</span>
                  <span className="text-xs text-muted-foreground">Pode dedicar algumas horas por dia para brincadeiras e cuidados</span>
                </label>
              </div>
              <div className={cardClass(form.tempoCuidar === "muito")}>
                <label htmlFor="tempo-muito" className="w-full h-full cursor-pointer flex flex-col gap-2">
                  <RadioGroupItem value="muito" id="tempo-muito" className="mb-2" />
                  <span className="font-medium">Muito tempo</span>
                  <span className="text-xs text-muted-foreground">Adora cuidar, brincar e passear com o pet todos os dias</span>
                </label>
              </div>
            </RadioGroup>
          </div>

          {/* 3. Estilo de vida */}
          <div>
            <Label className="text-base font-semibold">3. Qual dessas opções descreve melhor seu estilo de vida?</Label>
            <p className="text-sm text-muted-foreground mb-2">Pense na sua rotina diária e no tipo de companhia que está procurando.</p>
            <RadioGroup
              name="estiloVida"
              value={form.estiloVida}
              onValueChange={value => handleChange("estiloVida", value)}
              className="grid grid-cols-1 md:grid-cols-3 gap-3"
              required
            >
              <div className={cardClass(form.estiloVida === "tranquila")}>
                <label htmlFor="vida-tranquila" className="w-full h-full cursor-pointer flex flex-col gap-2">
                  <RadioGroupItem value="tranquila" id="vida-tranquila" className="mb-2" />
                  <span className="font-medium">Vida tranquila</span>
                  <span className="text-xs text-muted-foreground">Prefere pets calmos, gosta de relaxar e curtir momentos tranquilos</span>
                </label>
              </div>
              <div className={cardClass(form.estiloVida === "equilibrado")}>
                <label htmlFor="vida-equilibrado" className="w-full h-full cursor-pointer flex flex-col gap-2">
                  <RadioGroupItem value="equilibrado" id="vida-equilibrado" className="mb-2" />
                  <span className="font-medium">Ritmo equilibrado</span>
                  <span className="text-xs text-muted-foreground">Gosta de passear, brincar e também relaxar com o pet</span>
                </label>
              </div>
              <div className={cardClass(form.estiloVida === "acao")}>
                <label htmlFor="vida-acao" className="w-full h-full cursor-pointer flex flex-col gap-2">
                  <RadioGroupItem value="acao" id="vida-acao" className="mb-2" />
                  <span className="font-medium">Sempre em ação</span>
                  <span className="text-xs text-muted-foreground">Prefere pets ativos, gosta de esportes e muita energia</span>
                </label>
              </div>
            </RadioGroup>
          </div>

          {/* 4. Espaço */}
          <div>
            <Label className="text-base font-semibold">4. Como é o espaço da sua casa?</Label>
            <p className="text-sm text-muted-foreground mb-2">Descreva o ambiente onde seu pet vai viver.</p>
            <RadioGroup
              name="espaco"
              value={form.espaco}
              onValueChange={value => handleChange("espaco", value)}
              className="grid grid-cols-1 md:grid-cols-3 gap-3"
              required
            >
              <div className={cardClass(form.espaco === "pequeno")}>
                <label htmlFor="espaco-pequeno" className="w-full h-full cursor-pointer flex flex-col gap-2">
                  <RadioGroupItem value="pequeno" id="espaco-pequeno" className="mb-2" />
                  <span className="font-medium">Pequeno</span>
                  <span className="text-xs text-muted-foreground">Apartamento pequeno, sem área externa própria</span>
                </label>
              </div>
              <div className={cardClass(form.espaco === "area_interna")}>
                <label htmlFor="espaco-interno" className="w-full h-full cursor-pointer flex flex-col gap-2">
                  <RadioGroupItem value="area_interna" id="espaco-interno" className="mb-2" />
                  <span className="font-medium">Área interna</span>
                  <span className="text-xs text-muted-foreground">Apartamento ou casa com espaço interno amplo</span>
                </label>
              </div>
              <div className={cardClass(form.espaco === "quintal")}>
                <label htmlFor="espaco-quintal" className="w-full h-full cursor-pointer flex flex-col gap-2">
                  <RadioGroupItem value="quintal" id="espaco-quintal" className="mb-2" />
                  <span className="font-medium">Quintal</span>
                  <span className="text-xs text-muted-foreground">Tem quintal, jardim ou área externa para o pet brincar</span>
                </label>
              </div>
            </RadioGroup>
          </div>
        </div>
        <div className="flex gap-2 mt-6">
          <Button type="button" variant="outline" onClick={onBack} className="w-1/2">Voltar</Button>
          <Button type="submit" className="w-1/2">Próximo</Button>
        </div>
      </form>
    </>
  )
}
