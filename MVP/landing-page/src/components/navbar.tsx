import { ThemeToggle } from "@/components/theme-toggle"

export function Navbar() {
  return (
    <header className="fixed top-0 left-0 w-full border-b bg-background/80 backdrop-blur-md">
      <div className="mx-auto flex max-w-5xl items-center justify-between p-4">
        <h1 className="text-xl font-bold text-black dark:text-white">PetAffinity</h1>
        <ThemeToggle />
      </div>
    </header>
  )
}
