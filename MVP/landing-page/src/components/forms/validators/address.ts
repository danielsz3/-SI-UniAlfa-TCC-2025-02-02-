import { onlyDigits } from "./shared"

export function validateAddress(form: any) {
  const e: Record<string, string> = {}
  const cep = onlyDigits(form.cep)
  if (!cep || cep.length !== 8) e.cep = "CEP inválido (8 dígitos)."
  if (!form.logradouro) e.logradouro = "Logradouro é obrigatório."
  if (!form.numero) e.numero = "Número é obrigatório."
  if (!form.cidade) e.cidade = "Cidade é obrigatória."
  if (!form.estado || String(form.estado).length !== 2) e.estado = "UF deve ter 2 letras (ex: PR)."
  return e
}
