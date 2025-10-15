export function formatarDiferencaData(data: Date | string): string {
    const inicio = new Date(data)
    const agora = new Date()

    // Calcula diferença inicial em anos, meses e dias
    let anos = agora.getFullYear() - inicio.getFullYear()
    let meses = agora.getMonth() - inicio.getMonth()
    let dias = agora.getDate() - inicio.getDate()

    // Ajusta meses/dias negativos
    if (dias < 0) {
        meses -= 1
        const ultimoDiaMesAnterior = new Date(agora.getFullYear(), agora.getMonth(), 0).getDate()
        dias += ultimoDiaMesAnterior
    }

    if (meses < 0) {
        anos -= 1
        meses += 12
    }

    // Monta string final com pluralização
    const partes: string[] = []

    if (anos > 0) partes.push(`${anos} ${anos === 1 ? 'ano' : 'anos'}`)
    if (meses > 0) partes.push(`${meses} ${meses === 1 ? 'mês' : 'meses'}`)
    if (dias > 0 || partes.length === 0)
        partes.push(`${dias} ${dias === 1 ? 'dia' : 'dias'}`)

    // Junta partes com vírgulas e "e" no final
    if (partes.length > 1) {
        const ultimo = partes.pop()
        return partes.join(', ') + ' e ' + ultimo
    }
    return partes[0]
}

