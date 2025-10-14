// components/forms/inputs/CepField.tsx
"use client"
import { useState } from "react"
import TextField from "./TextField"

function onlyDigits(s?: string) {
  return (s || "").replace(/\D/g, "")
}

type Props = {
  value: string
  onChange: (v: string) => void
  onAddress: (addr: { logradouro?: string; bairro?: string; cidade?: string; uf?: string; complemento?: string }) => void
  error?: string
}

export default function CepField({ value, onChange, onAddress, error }: Props) {
  const [loading, setLoading] = useState(false)

  async function onBlur() {
    const cep = onlyDigits(value)
    if (cep.length !== 8) return
    setLoading(true)
    try {
      const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
      const data = await res.json()
      if (!data?.erro) {
        onAddress({
          logradouro: data.logradouro || "",
          bairro: data.bairro || "",
          cidade: data.localidade || "",
          uf: data.uf || "",
          complemento: data.complemento || "",
        })
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <TextField
      id="cep"
      name="cep"
      label="CEP"
      value={value}
      placeholder="CEP"
      onChange={(e) => onChange(e.target.value)}
      onBlur={onBlur}
      required
      maxLength={9}
      disabled={loading}
      error={error}
    />
  )
}
