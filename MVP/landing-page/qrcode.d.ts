declare module "qrcode" {
  interface ToDataURLOptions {
    errorCorrectionLevel?: "low" | "medium" | "quartile" | "high";
    type?: string;
    rendererOpts?: any;
    margin?: number;
    scale?: number;
    width?: number;
    color?: {
      dark?: string;
      light?: string;
    };
  }

  function toDataURL(
    text: string,
    options?: ToDataURLOptions
  ): Promise<string>;

  export { toDataURL };
  export default { toDataURL };
}
