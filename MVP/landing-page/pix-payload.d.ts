declare module "pix-payload" {
  interface PixPayloadOptions {
    pixKey: string;
    description?: string;
    merchantName: string;
    merchantCity: string;
    amount?: string;
  }

  export class PixPayload {
    constructor(options: PixPayloadOptions);
    getPayload(): string;
  }
}