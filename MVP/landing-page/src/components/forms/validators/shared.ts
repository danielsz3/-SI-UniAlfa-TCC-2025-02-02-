export const onlyDigits = (s?: string) => (s || "").replace(/\D/g, "")
export const isEmail = (email: string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email || "")
export const isDateISO = (s?: string) => /^\d{4}-\d{2}-\d{2}$/.test(s || "")
