"use client"

import Link from "next/link"
import { useEffect, useState } from "react"
import { useRouter } from "next/navigation"
import { ThemeToggle } from "@/components/theme-toggle"
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar"
import { LogIn, LogOut, User, PawPrint } from "lucide-react"

type MeResponse =
  | { authenticated: true; id?: number | string; name?: string; email?: string; avatarUrl?: string; role?: string }
  | { authenticated: false }

async function apiMe(token?: string): Promise<MeResponse> {
  try {
    const res = await fetch(`${process.env.NEXT_PUBLIC_API_BASE_URL}/me`, {
      method: "GET",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
    })
    if (!res.ok) return { authenticated: false }
    const data = await res.json()

    return {
      authenticated: true,
      id: data?.id,
      name: data?.nome ?? data?.name ?? data?.username,
      email: data?.email,
      avatarUrl: data?.avatar,
      role: data?.role ?? data?.perfil,
    }
  } catch {
    return { authenticated: false }
  }
}

async function apiLogout(token?: string): Promise<boolean> {
  try {
    const res = await fetch(`${process.env.NEXT_PUBLIC_API_BASE_URL}/logout`, {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
    })
    return res.ok
  } catch {
    return false
  }
}

export async function apiLogin(email: string, password: string): Promise<{ ok: boolean; error?: string }> {
  try {
    const res = await fetch(`${process.env.NEXT_PUBLIC_API_BASE_URL}/login`, {
      method: "POST",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, password }),
    })
    if (!res.ok) {
      const text = await res.text()
      return { ok: false, error: text || "Falha no login" }
    }

    // Se backend retornar token JSON e não usar cookie:
    // const data = await res.json()
    // localStorage.setItem("token", data?.access_token)

    return { ok: true }
  } catch (e: any) {
    return { ok: false, error: e?.message ?? "Erro de rede" }
  }
}

// Subcomponentes
function Brand() {
  return (
    <Link href="/" className="flex items-center gap-2">
      <PawPrint className="h-5 w-5 text-primary" aria-hidden="true" />
      <span className="text-xl font-bold tracking-tight">PetAffinity</span>
    </Link>
  )
}

function NavLink({ href, children }: { href: string; children: React.ReactNode }) {
  return (
    <Link
      href={href}
      className="text-sm font-medium text-muted-foreground transition-colors hover:text-primary"
    >
      {children}
    </Link>
  )
}

function CenterNav() {
  return (
    <nav className="hidden md:flex items-center gap-8">
      <NavLink href="/adotar">ADOTAR UM PET</NavLink>
      <NavLink href="/doar-ong">DOAR PARA A ONG</NavLink>
      <NavLink href="/doar-pet">DOAR UM PET</NavLink>
      <NavLink href="/sobre">SOBRE</NavLink>
    </nav>
  )
}

function RightActions({
  me,
  onLogoutClick,
}: {
  me: MeResponse
  onLogoutClick: () => Promise<void>
}) {
  return (
    <div className="flex items-center gap-2">
      <ThemeToggle />
      {me.authenticated ? (
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button variant="ghost" size="icon" className="relative">
              <Avatar className="h-8 w-8">
                <AvatarImage src={(me as any).avatarUrl} alt={(me as any).name ?? "Usuário"} />
                <AvatarFallback>
                  {(me as any).name ? (me as any).name[0]?.toUpperCase() : "U"}
                </AvatarFallback>
              </Avatar>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" className="w-56">
            <DropdownMenuLabel>
              <div className="flex flex-col">
                <span className="font-medium leading-tight truncate">{(me as any).name ?? "Usuário"}</span>
                <span className="text-xs text-muted-foreground truncate">{(me as any).email ?? ""}</span>
              </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
              <Link href="/perfil" className="flex items-center gap-2">
                <User className="h-4 w-4" />
                Perfil
              </Link>
            </DropdownMenuItem>
            {/* Exemplo: link para área admin se role === 'admin' */}
            {(me as any).role === "admin" && (
              <>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                  <Link href="/admin" className="flex items-center gap-2">
                    Painel Admin
                  </Link>
                </DropdownMenuItem>
              </>
            )}
            <DropdownMenuSeparator />
            <DropdownMenuItem
              className="text-red-600 focus:text-red-600"
              onClick={onLogoutClick}
            >
              <LogOut className="h-4 w-4 mr-2" />
              Sair
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      ) : (
        <Button asChild variant="ghost" className="gap-2">
          <Link href="/login">
            <LogIn className="h-4 w-4" />
            Entrar
          </Link>
        </Button>
      )}
    </div>
  )
}

export function Navbar() {
  const router = useRouter()
  const [me, setMe] = useState<MeResponse>({ authenticated: false })
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let active = true
    ;(async () => {
      const token = localStorage.getItem("token") || undefined
      const res = await apiMe(token)
      if (active) {
        setMe(res)
        setLoading(false)
      }
    })()
    return () => {
      active = false
    }
  }, [])

  const handleLogout = async () => {
    const token = localStorage.getItem("token") || undefined
    const ok = await apiLogout(token)

    localStorage.removeItem("token")

    if (ok) {
      setMe({ authenticated: false })
      router.refresh() 
      router.push("/") 
    }
  }

  return (
    <header className="fixed inset-x-0 top-0 z-50 border-b bg-background/80 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 md:py-4">
        <Brand />
        <CenterNav />
        <RightActions me={me} onLogoutClick={handleLogout} />
      </div>
    </header>
  )
}
