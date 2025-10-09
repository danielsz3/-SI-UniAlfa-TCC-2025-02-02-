"use client";

import { useState } from "react";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { useRouter } from "next/navigation";

export default function ConfirmForm({ data, onBack }: any) {
  const router = useRouter();

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    setLoading(true);

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/usuarios`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      });

      if (!res.ok) {
        const errorData = await res.json().catch(() => null);
        throw new Error(errorData?.message || "Erro ao cadastrar usuário");
      }

      setSuccess(true);
      // Opcional: redirecionar após sucesso, ex:
      // router.push("/login");
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  if (success) {
    return (
      <div className="w-full max-w-2xl mx-auto bg-white dark:bg-slate-800 rounded-lg shadow-lg p-8 mt-8 text-center">
        <h2 className="text-2xl font-bold mb-6 text-slate-900 dark:text-white">Cadastro realizado com sucesso!</h2>
        <p className="mb-4 text-slate-700 dark:text-slate-300">Você já pode fazer login.</p>
        <Button onClick={() => router.push("/login")}>Ir para Login</Button>
      </div>
    );
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

      {error && <p className="text-red-600 text-center mt-4">{error}</p>}

      <div className="flex gap-2 mt-6">
        <Button type="button" variant="outline" onClick={onBack} className="w-1/2" disabled={loading}>
          Voltar
        </Button>
        <Button type="submit" className="w-1/2" disabled={loading}>
          {loading ? "Enviando..." : "Confirmar Cadastro"}
        </Button>
      </div>
    </form>
  );
}
