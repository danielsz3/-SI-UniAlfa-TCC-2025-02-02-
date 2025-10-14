"use client"
import { useState } from "react"
import { Button } from "@/components/ui/button"
import TextField from "@/components/forms/inputs/TextField"
import CepField from "@/components/forms/inputs/CepField"
import { validateAddress } from "@/components/forms/validators/address"

export default function AddressForm({ onNext, onBack, defaultValues = {} }: any) {
  const [form, setForm] = useState({
    cep: defaultValues.cep || "",
    logradouro: defaultValues.logradouro || "",
    complemento: defaultValues.complemento || "",
    numero: defaultValues.numero || "",
    bairro: defaultValues.bairro || "",
    cidade: defaultValues.cidade || "",
    estado: defaultValues.estado || "",
  })
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [loading, setLoading] = useState(false)

  const handle = (e: React.ChangeEvent<HTMLInputElement>) =>
    setForm({ ...form, [e.target.name]: e.target.value })

  function submit(e: React.FormEvent) {
    e.preventDefault()
    const eobj = validateAddress(form)
    setErrors(eobj)
    if (Object.keys(eobj).length) return
    onNext(form)
  }

  return (
    <form className="space-y-6 w-full max-w-md mx-auto bg-white dark:bg-slate-800 rounded-lg shadow-lg p-8 mt-8" onSubmit={submit}>
      <h2 className="text-xl font-bold mb-4 text-slate-900 dark:text-white">Endereço</h2>

      <CepField
        value={form.cep}
        onChange={(v) => setForm({ ...form, cep: v })}
        onAddress={(addr) => setForm(f => ({ ...f, ...addr }))}
        error={errors.cep}
      />

      <TextField id="logradouro" name="logradouro" label="Logradouro" value={form.logradouro} onChange={handle} required error={errors.logradouro}/>
      <TextField id="complemento" name="complemento" label="Complemento" value={form.complemento} onChange={handle}/>
      <TextField id="numero" name="numero" label="Número" value={form.numero} onChange={handle} required error={errors.numero}/>
      <TextField id="bairro" name="bairro" label="Bairro" value={form.bairro} onChange={handle}/>
      <TextField id="cidade" name="cidade" label="Cidade" value={form.cidade} onChange={handle} required error={errors.cidade}/>
      <TextField id="estado" name="estado" label="UF" value={form.estado} onChange={handle} required maxLength={2} placeholder="PR" error={errors.estado}/>

      <div className="flex gap-2">
        <Button type="button" variant="outline" onClick={onBack} className="w-1/2" disabled={loading}>Voltar</Button>
        <Button type="submit" className="w-1/2" disabled={loading}>Próximo</Button>
      </div>
    </form>
  )
}
