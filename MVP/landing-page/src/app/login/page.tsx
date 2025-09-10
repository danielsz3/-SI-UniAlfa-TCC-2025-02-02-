import { Navbar } from "@/components/navbar"
import { Button } from "@/components/ui/button"
import Link from "next/link"

export default function LoginPage() {
    return (
        <>
            <Navbar />
            <main className="flex min-h-screen items-center justify-center">
                <div className=
                    "w-full max-w-md rounded-lg bg-white dark:bg-slate-800 shadow-lg p-8">
                    <h2 className=
                        "text-2xl font-bold mb-6 text-center text-slate-900 dark:text-white"
                    >
                        Entrar
                    </h2>
                    <form className="space-y-4">
                        <div>
                            <label
                                htmlFor="email"
                                className=
                                "block text-sm font-medium text-slate-700 dark:text-slate-200">
                                E-mail
                            </label>
                            <input
                                id="email"
                                type="email"
                                required
                                className=
                                "mt-1 w-full rounded border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="seu@email.com"
                            />
                        </div>
                        <div>
                            <label htmlFor="password"
                                className=
                                "block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Senha
                            </label>
                            <input
                                id="password"
                                type="password"
                                required
                                className=
                                "mt-1 w-full rounded border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="Sua senha"
                            />
                        </div>
                        <Button type="submit" className="w-full">Entrar</Button>
                        <div className="flex justify-center">
                            <Link href="/reset-password"
                                className="text-sm text-primary hover:underline mt-2">
                                Esqueci minha senha
                            </Link>
                        </div>
                    </form>
                    <p className="mt-4 text-center text-sm text-slate-600 dark:text-slate-300">
                        Não tem uma conta?{" "}
                        <Link href="/register" className="text-primary font-medium hover:underline">
                            Cadastre-se
                        </Link>
                    </p>
                </div>
            </main>
        </>
    )
}
