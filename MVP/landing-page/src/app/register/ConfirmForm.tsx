"use client"

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from "@/components/ui/button"

export default function ConfirmForm({ data, onBack }: any) {
  function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    // Aqui você pode enviar os dados para a API
    alert("Cadastro realizado com sucesso!")
  }

  return (
    <form className="w-full max-w-2xl mx-auto bg-white dark:bg-slate-800 rounded-lg shadow-lg p-8 mt-8" onSubmit={handleSubmit}>
      <h2 className="text-2xl font-bold mb-6 text-center text-slate-900 dark:text-white">Confirme seus dados</h2>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Dado</TableHead>
            <TableHead>Valor</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow><TableCell>Nome</TableCell><TableCell>{data.nome}</TableCell></TableRow>
          <TableRow><TableCell>Telefone</TableCell><TableCell>{data.telefone}</TableCell></TableRow>
          <TableRow><TableCell>E-mail</TableCell><TableCell>{data.email}</TableCell></TableRow>
          <TableRow><TableCell>CEP</TableCell><TableCell>{data.cep}</TableCell></TableRow>
          <TableRow><TableCell>Logradouro</TableCell><TableCell>{data.logradouro}</TableCell></TableRow>
          <TableRow><TableCell>Complemento</TableCell><TableCell>{data.complemento}</TableCell></TableRow>
          <TableRow><TableCell>Número</TableCell><TableCell>{data.numero}</TableCell></TableRow>
          <TableRow><TableCell>Cidade</TableCell><TableCell>{data.cidade}</TableCell></TableRow>
          <TableRow><TableCell>Estado</TableCell><TableCell>{data.estado}</TableCell></TableRow>
          <TableRow><TableCell>Tamanho do pet</TableCell><TableCell>{data.tamanhoPet}</TableCell></TableRow>
          <TableRow><TableCell>Tempo hábil para cuidar</TableCell><TableCell>{data.tempoCuidar}</TableCell></TableRow>
          <TableRow><TableCell>Estilo de vida</TableCell><TableCell>{data.estiloVida}</TableCell></TableRow>
          <TableRow><TableCell>Espaço</TableCell><TableCell>{data.espaco}</TableCell></TableRow>
        </TableBody>
      </Table>
      <div className="flex gap-2 mt-6">
        <Button type="button" variant="outline" onClick={onBack} className="w-1/2">Voltar</Button>
        <Button type="submit" className="w-1/2">Confirmar Cadastro</Button>
      </div>
    </form>
  )
}