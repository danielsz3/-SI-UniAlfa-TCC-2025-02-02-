import { fetchUtils, DataProvider } from "react-admin";
import simpleRestProvider from "ra-data-simple-rest";

const apiUrl = "http://127.0.0.1:8000/api";

const httpClient = (url: string, options: fetchUtils.Options = {}) => {
  if (!options.headers) {
    options.headers = new Headers({ Accept: "application/json" });
  }
  const token = localStorage.getItem("authToken");
  if (token) {
    (options.headers as Headers).set("Authorization", `Bearer ${token}`);
  }
  return fetchUtils.fetchJson(url, options);
};

// Cria o provider base
const baseDataProvider = simpleRestProvider(apiUrl, httpClient);

// Função para converter dados em FormData quando necessário
const convertDataRequestToHTTP = (data: any, isUpdate = false): { data: string | FormData; headers: Record<string, string> } => {
  const requestData = { ...data };
  
  // No update, remove campos de arquivo que não foram alterados (não têm rawFile)
  if (isUpdate) {
    Object.keys(requestData).forEach(key => {
      const field = requestData[key];
      if (key === 'arquivo' && !field.rawFile) {
        delete requestData[key];
      }
    });
  }
  
  // Verifica se algum campo tem a propriedade rawFile (indicando upload)
  const hasFileUpload = Object.keys(requestData).some(key => {
    const field = requestData[key];
    return field && typeof field === 'object' && field.rawFile instanceof File;
  });

  if (!hasFileUpload) {
    return {
      data: JSON.stringify(requestData),
      headers: { 'Content-Type': 'application/json' }
    };
  }

  // Se tem upload, cria FormData
  const formData = new FormData();
  
  Object.keys(requestData).forEach(key => {
    const field = requestData[key];
    
    if (field && typeof field === 'object' && field.rawFile instanceof File) {
      // Campo de arquivo - adiciona o arquivo
      formData.append(key, field.rawFile, field.title || field.rawFile.name);
    } else if (Array.isArray(field)) {
      // Array de arquivos
      field.forEach((item, index) => {
        if (item && typeof item === 'object' && item.rawFile instanceof File) {
          formData.append(`${key}[${index}]`, item.rawFile, item.title || item.rawFile.name);
        } else if (item !== null && item !== undefined) {
          formData.append(`${key}[${index}]`, String(item));
        }
      });
    } else if (field !== null && field !== undefined && field !== '') {
      // Campo normal
      formData.append(key, String(field));
    }
  });

  return {
    data: formData,
    headers: {} // Não definir Content-Type para FormData
  };
};

// Customiza o data provider
export const dataProvider: DataProvider = {
  ...baseDataProvider,

  create: async (resource: string, params: any) => {
    const token = localStorage.getItem("authToken");
    const { data, headers } = convertDataRequestToHTTP(params.data, false);
    
    const requestHeaders: Record<string, string> = { ...headers };
    if (token) {
      requestHeaders['Authorization'] = `Bearer ${token}`;
    }

    try {
      const response = await fetch(`${apiUrl}/${resource}`, {
        method: 'POST',
        body: data,
        headers: requestHeaders,
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(`HTTP error! status: ${response.status} - ${JSON.stringify(errorData)}`);
      }

      const json = await response.json();
      return { 
        data: json,
        redirectTo: 'list' // Redireciona para a lista após criar
      };
    } catch (error) {
      console.error('Erro no create:', error);
      throw error;
    }
  },

  update: async (resource: string, params: any) => {
    const token = localStorage.getItem("authToken");
    const { data, headers } = convertDataRequestToHTTP(params.data, true);
    
    const requestHeaders: Record<string, string> = { ...headers };
    if (token) {
      requestHeaders['Authorization'] = `Bearer ${token}`;
    }

    try {
      const response = await fetch(`${apiUrl}/${resource}/${params.id}`, {
        method: 'PUT',
        body: data,
        headers: requestHeaders,
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(`HTTP error! status: ${response.status} - ${JSON.stringify(errorData)}`);
      }

      const json = await response.json();
      return { 
        data: json,
        redirectTo: 'list' // Redireciona para a lista após editar
      };
    } catch (error) {
      console.error('Erro no update:', error);
      throw error;
    }
  },
};