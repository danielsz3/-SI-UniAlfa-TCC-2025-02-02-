import { fetchUtils, DataProvider } from "react-admin";
import simpleRestProvider from "ra-data-simple-rest";

const apiUrl = "http://127.0.0.1:8000/api";

// Função utilitária para requisições com autenticação (Token)
// httpClient ajustado para lidar com FormData E Headers
const httpClient = (url: string, options: fetchUtils.Options = {}) => {

  // 1. GARANTE que options.headers é um objeto Headers
  const finalHeaders = new Headers(options.headers || {});
  options.headers = finalHeaders; // Atualiza options.headers com a instância correta

  // 2. Lógica de Autenticação (Agora segura para usar .set())
  if (!finalHeaders.has("Accept")) {
    finalHeaders.set("Accept", "application/json");
  }
  const token = localStorage.getItem("authToken");
  if (token) {
    finalHeaders.set("Authorization", `Bearer ${token}`);
  }

  // 3. Verifica se o corpo (body) é um FormData
  if (options.body instanceof FormData) {

    // Para FormData, DELETAMOS Content-Type, pois o navegador lida com isso.
    finalHeaders.delete("Content-Type");

    // ... restante da lógica para FormData usando fetch nativo (mantido)
    return fetch(url, options as RequestInit).then(response => {
      if (!response.ok) {
        return response.json()
          .then(errorBody => {
            return Promise.reject({
              status: response.status,
              message: errorBody.message || 'Erro de rede', // Use a mensagem da API se existir
            });
          })
          .catch(() => {
            // Caso o corpo da resposta não seja um JSON válido
            return Promise.reject({ status: response.status });
          });
      }
      // Se a resposta for bem-sucedida, continue como antes
      return response.json().then(json => ({
        status: response.status,
        headers: response.headers,
        body: '',
        json: json
      }));
    });
  }

  // 4. Se for JSON ou outra requisição, usa o fetchUtils.fetchJson
  return fetchUtils.fetchJson(url, options);
};

// Cria o provider base com o httpClient customizado (para métodos GET, GET_ONE, GET_LIST, etc.)
const baseDataProvider = simpleRestProvider(apiUrl, httpClient);

// Função para converter dados para JSON ou FormData (Upload de Arquivos)
const convertDataRequestToHTTP = (data: any, isUpdate = false): { data: string | FormData; headers: Record<string, string> } => {
  const requestData = { ...data };

  // Lógica de remoção de campos de arquivo não alterados (comum em updates)
  if (isUpdate) {
    Object.keys(requestData).forEach(key => {
      const field = requestData[key];
      if (key === 'arquivo' && !field?.rawFile) {
        delete requestData[key];
      }
    });
  }

  // Verifica se há upload de arquivo (se algum campo possui rawFile)
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

  // Se houver upload, monta o FormData
  const formData = new FormData();

  Object.keys(requestData).forEach(key => {
    const field = requestData[key];

    if (field && typeof field === 'object' && field.rawFile instanceof File) {
      // Arquivo: Adiciona o File ao FormData
      formData.append(key, field.rawFile, field.title || field.rawFile.name);
    } else if (Array.isArray(field)) {
      // Lógica de tratamento para arrays (ex: múltiplos uploads ou IDs)
      field.forEach((item, index) => {
        if (item && typeof item === 'object' && item.rawFile instanceof File) {
          formData.append(`${key}[${index}]`, item.rawFile, item.title || item.rawFile.name);
        } else if (item !== null && item !== undefined) {
          formData.append(`${key}[${index}]`, String(item));
        }
      });
    } else if (field !== null && field !== undefined && field !== '') {
      // Campos simples
      formData.append(key, String(field));
    }
  });

  return {
    data: formData,
    headers: {} // O browser define o Content-Type para FormData
  };
};

// Customiza o data provider para sobrescrever métodos
export const dataProvider: DataProvider = {
  ...baseDataProvider,

  /**
   * CREATE (Criação de Recurso)
   * Sobrescrito para suportar o envio de FormData (Upload de Arquivos).
   */
  create: async (resource: string, params: any) => {
    // Converte os dados para body e headers
    const { data: body, headers } = convertDataRequestToHTTP(params.data, false);

    // Usa o httpClient para enviar a requisição de forma padronizada
    const response = await httpClient(`${apiUrl}/${resource}`, {
      method: 'POST',
      body: body,
      headers: headers,
    });

    return {
      data: response.json,
      redirectTo: 'list'
    };
  },

  /**
   * UPDATE (Atualização de Recurso)
   * Sobrescrito para suportar o envio de FormData (Upload de Arquivos).
   */
  update: async (resource: string, params: any) => {
    // Converte os dados para body e headers
    const { data: body, headers } = convertDataRequestToHTTP(params.data, true);

    // Usa o httpClient para enviar a requisição de forma padronizada
    const response = await httpClient(`${apiUrl}/${resource}/${params.id}`, {
      method: 'PUT',
      body: body,
      headers: headers,
    });

    return {
      data: response.json,
      redirectTo: 'list'
    };
  },

  /**
   * DELETEMANY (Exclusão em Massa)
   * Sobrescrito para garantir a robustez contra IDs nulos e retornar
   * os IDs deletados quando a API não retorna um corpo de resposta.
   */
  deleteMany: (resource, params) => {
    // ... sua lógica de exclusão em massa aqui (usa httpClient naturalmente)
    const idsToDelete = params.ids.filter(id => id);

    if (idsToDelete.length === 0) {
      return Promise.resolve({ data: [] });
    }

    return Promise.all(
      idsToDelete.map(id =>
        httpClient(`${apiUrl}/${resource}/${encodeURIComponent(id)}`, {
          method: 'DELETE',
          headers: new Headers({
            'Content-Type': 'text/plain',
          }),
        })
      )
    ).then(() => {
      return {
        data: idsToDelete
      };
    });
  },
};