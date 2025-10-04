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

    if (isUpdate) {
        Object.keys(requestData).forEach(key => {
            const field = requestData[key];
            if ((key === 'arquivo' || key === 'imagens') && field && typeof field === 'object' && !field.rawFile) {
                delete requestData[key];
            }
        });
    }

    const hasFileUpload = Object.keys(requestData).some(key => {
        const field = requestData[key];
        return Array.isArray(field)
            ? field.some(item => item && typeof item === 'object' && item.rawFile instanceof File)
            : field && typeof field === 'object' && field.rawFile instanceof File;
    });

    if (!hasFileUpload) {
        return {
            data: JSON.stringify(requestData),
            headers: { 'Content-Type': 'application/json' }
        };
    }

    const formData = new FormData();

    // Função auxiliar para verificar se é uma string de data ISO 8601 (para o caso de a data vir como string no objeto)
    const isIsoDateString = (value: string) => {
        if (typeof value !== 'string') return false;
        return /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?Z$/.test(value.replace(/"/g, ''));
    };

    Object.keys(requestData).forEach(key => {
        const field = requestData[key];

        // 1. Lógica para CAMPO DE ARQUIVO ÚNICO (Mantida)
        if (field && typeof field === 'object' && field.rawFile instanceof File) {
            formData.append(key, field.rawFile, field.title || field.rawFile.name);

        // 2. Lógica para ARRAY (Mantida)
        } else if (Array.isArray(field)) {
            
            const hasFileInArray = field.some(item => item && typeof item === 'object' && item.rawFile instanceof File);
            
            if (hasFileInArray) {
                field.forEach((item) => {
                    if (item && typeof item === 'object' && item.rawFile instanceof File) {
                        formData.append(`${key}[]`, item.rawFile, item.title || item.rawFile.name);
                    } else if (item !== null && item !== undefined) {
                         formData.append(`${key}[]`, JSON.stringify(item));
                    }
                });
            } else {
                formData.append(key, JSON.stringify(field));
            }

        // 3. Lógica para OUTROS OBJETOS COMPLEXOS (Endereço, Data de Nascimento como Objeto Date)
        } else if (field && typeof field === 'object' && field !== null) {
            
            let valueToAppend;

            // Se for uma instância de Data (objeto nativo Date do JS)
            if (field instanceof Date) {
                valueToAppend = field.toISOString(); // Converte para o formato ISO sem aspas
            
            // Se for o campo 'data_nascimento' e já for uma string ISO 8601 (que pode vir como objeto no estado)
            } else if (key === 'data_nascimento' || isIsoDateString(field)) {
                // Remove as aspas se a string estiver encapsulada
                valueToAppend = String(field).replace(/^"|"$/g, '');
            
            // Se for qualquer outro objeto complexo (como 'endereço')
            } else {
                // Serializa o objeto completo para JSON
                valueToAppend = JSON.stringify(field);
            }
            
            // GARANTIA: Adiciona o valor serializado ou a string de data ao FormData
            if (valueToAppend !== undefined) {
                 formData.append(key, valueToAppend);
            }

        // 4. Lógica para CAMPOS SIMPLES (Mantida)
        } else if (field !== null && field !== undefined && field !== '') {
            // Este bloco agora trata apenas strings, números e booleanos simples
            formData.append(key, String(field));
        }
    });

    return {
        data: formData,
        headers: {}
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