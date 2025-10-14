"use client"
import { Label } from "@/components/ui/label"
import { Input } from "@/components/ui/input"

type Props = {
  id: string
  name: string
  label: string
  type?: string
  value: string
  placeholder?: string
  onChange: (e: React.ChangeEvent<HTMLInputElement>) => void
  onBlur?: (e: React.FocusEvent<HTMLInputElement>) => void
  required?: boolean
  minLength?: number
  maxLength?: number
  disabled?: boolean
  error?: string
  className?: string
}

export default function TextField({
  id, name, label, type = "text", value, placeholder,
  onChange, onBlur, required, minLength, maxLength, disabled, error, className
}: Props) {
  return (
    <div className={className || "grid gap-2"}>
      <Label htmlFor={id}>{label}</Label>
      <Input
        id={id}
        name={name}
        type={type}
        value={value}
        placeholder={placeholder}
        onChange={onChange}
        onBlur={onBlur}
        required={required}
        minLength={minLength}
        maxLength={maxLength}
        disabled={disabled}
      />
      {error && <span className="text-red-500 text-xs">{error}</span>}
    </div>
  )
}
