import { fetchUtils, DataProvider } from "react-admin";
import simpleRestProvider from "ra-data-simple-rest";

const apiUrl = "http://127.0.0.1:8000/api";

// --- (NOVO) Helper para processar erros da API ---
/**
 * Esta função recebe uma resposta de erro (não-ok) e a transforma
 * no formato de erro que o React-Admin espera.
 */
const handleError = (response: Response) => {
    return response.json().then((errorBody) => {
        // Caso 1: Erro de Validação (ex: 422 do Laravel)
        if (response.status === 422 && errorBody.errors) {
            const formattedErrors: { [key: string]: string } = {};

            // Mapeia { "field": ["message1", ...] } para { "field": "message1" }
            Object.keys(errorBody.errors).forEach(key => {
                if (Array.isArray(errorBody.errors[key]) && errorBody.errors[key].length > 0) {
                    formattedErrors[key] = errorBody.errors[key][0];
                }
            });

            // Rejeita a promise com o formato que o react-admin entende
            return Promise.reject({
                status: response.status,
                message: errorBody.message || "Erro de validação", // Mensagem genérica para a notificação
                body: { errors: formattedErrors } // Erros específicos para os campos
            });
        }

        // Caso 2: Outros erros (ex: 401, 403, 500)
        // Rejeita com uma mensagem simples para a notificação
        return Promise.reject({
            status: response.status,
            message: errorBody.message || `Erro ${response.status}`
        });
    }).catch(() => {
        // Caso o corpo do erro não seja um JSON válido
        return Promise.reject({ 
            status: response.status, 
            message: response.statusText || 'Erro de rede' 
        });
    });
};

// --- (ATUALIZADO) httpClient unificado ---
/**
 * Este httpClient agora trata TODAS as requisições (JSON e FormData)
 * e usa o 'handleError' para formatar os erros.
 */
const httpClient = (url: string, options: fetchUtils.Options = {}) => {
    const finalHeaders = new Headers(options.headers || {});
    options.headers = finalHeaders;

    if (!finalHeaders.has("Accept")) {
        finalHeaders.set("Accept", "application/json");
    }

    const token = localStorage.getItem("authToken");
    if (token) {
        finalHeaders.set("Authorization", `Bearer ${token}`);
    }

    // Se o corpo for FormData, o 'fetch' define o Content-Type automaticamente
    if (options.body instanceof FormData) {
        finalHeaders.delete("Content-Type"); 
    }

    return fetch(url, options as RequestInit)
        .then((response) => {
            // Se a resposta não for OK (ex: 4xx, 5xx), usamos nosso helper
            if (!response.ok) {
                return handleError(response);
            }
            
            // Trata respostas sem corpo (ex: DELETE 204)
            if (response.status === 204 || response.status === 205) {
                return {
                    status: response.status,
                    headers: response.headers,
                    body: "",
                    json: null, // ra-data-simple-rest espera a propriedade 'json'
                };
            }

            // Resposta OK com corpo
            return response.json().then((json) => ({
                status: response.status,
                headers: response.headers,
                body: "", // 'body' não é mais usado, 'json' é o principal
                json: json, // ra-data-simple-rest usa 'json'
            }));
        })
        .catch((error) => {
            // Pega erros de rede (ex: 'fetch' falhou, DNS, CORS)
            
            // Se já for um erro formatado por nós, apenas repassa
            if (error.status) {
                return Promise.reject(error);
            }
            
            // Se for um erro de rede (ex: TypeError: Failed to fetch)
            console.error("Erro de rede no httpClient:", error);
            return Promise.reject({
                status: 0, // 0 para erros de rede
                message: error.message || "Não foi possível conectar à API"
            });
        });
};

const baseDataProvider = simpleRestProvider(apiUrl, httpClient);

const convertDataRequestToHTTP = (
  data: any,
  isUpdate = false
): { data: string | FormData; headers: Record<string, string>; hasFile: boolean } => {
  const requestData = { ...data };

  if (isUpdate) {
    Object.keys(requestData).forEach((key) => {
      const field = requestData[key];
      if (
        (key === "arquivo" || key === "imagem") &&
        field &&
        typeof field === "object" &&
        !field.rawFile
      ) {
        delete requestData[key];
      }
    });
  }

  const hasFileUpload = Object.keys(requestData).some((key) => {
    const field = requestData[key];
    return Array.isArray(field)
      ? field.some(
          (item) => item && typeof item === "object" && item.rawFile instanceof File
        )
      : field && typeof field === "object" && field.rawFile instanceof File;
  });

  if (!hasFileUpload) {
    return {
      data: JSON.stringify(requestData),
      headers: { "Content-Type": "application/json" },
      hasFile: false,
    };
  }

  const formData = new FormData();

  if (isUpdate) {
    formData.append("_method", "PUT");
  }

  const isIsoDateString = (value: string) => {
    if (typeof value !== "string") return false;
    return /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?Z$/.test(
      value.replace(/"/g, "")
    );
  };

  Object.keys(requestData).forEach((key) => {
    const field = requestData[key];

    if (field && typeof field === "object" && field.rawFile instanceof File) {
      formData.append(key, field.rawFile, field.title || field.rawFile.name);
    } else if (Array.isArray(field)) {
      const hasFileInArray = field.some(
        (item) => item && typeof item === "object" && item.rawFile instanceof File
      );

      if (hasFileInArray) {
        field.forEach((item) => {
          if (item && typeof item === "object" && item.rawFile instanceof File) {
            formData.append(`${key}[]`, item.rawFile, item.title || item.rawFile.name);
          } else if (item !== null && item !== undefined) {
            formData.append(`${key}[]`, JSON.stringify(item));
          }
        });
      } else {
        formData.append(key, JSON.stringify(field));
      }
    } else if (field && typeof field === "object" && field !== null) {
      let valueToAppend;
      if (field instanceof Date) {
        valueToAppend = field.toISOString();
      } else if (key.startsWith("data_") || isIsoDateString(field)) {
        valueToAppend = String(field).replace(/^"|"$/g, "");
      } else {
        valueToAppend = JSON.stringify(field);
      }
      if (valueToAppend !== undefined) {
        formData.append(key, valueToAppend);
      }
    } else if (field !== null && field !== undefined && field !== "") {
      formData.append(key, String(field));
    }
  });

  return {
    data: formData,
    headers: {},
    hasFile: true,
  };
};

// Data Provider principal
export const dataProvider: DataProvider = {
  ...baseDataProvider,

  // CREATE
  create: async (resource: string, params: any) => {
    const { data: body, headers } = convertDataRequestToHTTP(params.data, false);
    const response = await httpClient(`${apiUrl}/${resource}`, {
      method: "POST",
      body: body,
      headers: headers,
    });

    return {
      data: response.json,
      redirectTo: "list",
    };
  },

  // UPDATE (com suporte a upload via POST + _method=PUT)
  update: async (resource: string, params: any) => {
    const { data: body, headers, hasFile } = convertDataRequestToHTTP(params.data, true);

    const response = await httpClient(`${apiUrl}/${resource}/${params.id}`, {
      method: hasFile ? "POST" : "PUT",
      body: body,
      headers: headers,
    });

    return {
      data: response.json,
      redirectTo: "list",
    };
  },

  // DELETEMANY
  deleteMany: (resource, params) => {
    const idsToDelete = params.ids.filter((id) => id);
    if (idsToDelete.length === 0) {
      return Promise.resolve({ data: [] });
    }

    return Promise.all(
      idsToDelete.map((id) =>
        httpClient(`${apiUrl}/${resource}/${encodeURIComponent(id)}`, {
          method: "DELETE",
          headers: new Headers({
            "Content-Type": "text/plain",
          }),
        })
      )
    ).then(() => {
      return { data: idsToDelete };
    });
  },

  // GETONE (normaliza campos de arquivos e imagens)
  getOne: async (resource: string, params: any) => {
    const response = await httpClient(`${apiUrl}/${resource}/${params.id}`, {
      method: "GET",
    });

    const data = response.json;
    const storageBaseUrl = import.meta.env.VITE_API_URL;

    if (data.arquivo && typeof data.arquivo === "string") {
      data.arquivo = {
        src: `${storageBaseUrl}${data.arquivo}`,
        title: data.titulo || data.arquivo.split("/").pop(),
      };
    }

    if (data.imagem && typeof data.imagem === "string") {
      data.imagem = {
        src: `${storageBaseUrl}/imagens/${data.imagem}`,
        title: data.nome + "_logo" || data.imagem.split("/").pop(),
      };
    }

    if (Array.isArray(data.imagens)) {
      data.imagens = data.imagens.map((img: any) => ({
        src: `${storageBaseUrl}/imagens/${img.caminho}`,
        title: img.caminho.split("/").pop(),
      }));
    }

    return { data };
  },
};