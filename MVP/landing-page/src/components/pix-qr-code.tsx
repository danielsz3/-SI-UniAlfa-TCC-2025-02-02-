"use client";

import { useMemo } from "react";
import * as PixPayloadModule from "pix-payload";

const PixPayload = PixPayloadModule.PixPayload;

interface PixQRCodeExternalProps {
  pixKey: string;
  merchantName: string;
  merchantCity: string;
  amount?: number;
}

export function PixQRCodeExternal({ pixKey, merchantName, merchantCity, amount }: PixQRCodeExternalProps) {
  const payload = useMemo(() => {
    try {
      const pixPayload = new PixPayload({
        pixKey,
        merchantName,
        merchantCity,
        amount: amount ? amount.toFixed(2) : undefined,
        description: "Doação PetAffinity",
      });
      return pixPayload.getPayload();
    } catch (error) {
      console.error("Erro ao gerar payload Pix:", error);
      return "";
    }
  }, [pixKey, merchantName, merchantCity, amount]);

  if (!payload) return <p>Gerando QR Code...</p>;

  const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(payload)}&size=256x256`;

  return (
    <div className="bg-white p-4 inline-block">
      <img src={qrCodeUrl} alt="QR Code Pix" width={256} height={256} />
    </div>
  );
}
