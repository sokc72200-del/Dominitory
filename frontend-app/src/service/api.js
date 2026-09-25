const API_BASE = import.meta.env.VITE_API_BASE_URL || "/api";

async function request(url, options = {}) {
  const config = {
    headers: {
      Accept: "application/json",
      ...(options.headers || {}),
    },
    ...options,
  };

  if (config.body && !(config.body instanceof FormData)) {
    config.headers["Content-Type"] ??= "application/json";
  }

  const response = await fetch(`${API_BASE}${url}`, config);
  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(data.message || data.error || "Request failed");
    error.status = response.status;
    error.response = { status: response.status, data };
    throw error;
  }

  return data;
}

export default {
  get(url, options = {}) {
    return request(url, { ...options, method: "GET" });
  },
  post(url, body, options = {}) {
    return request(url, {
      ...options,
      method: "POST",
      body: body instanceof FormData ? body : JSON.stringify(body ?? {}),
    });
  },
  put(url, body, options = {}) {
    return request(url, {
      ...options,
      method: "PUT",
      body: body instanceof FormData ? body : JSON.stringify(body ?? {}),
    });
  },
  del(url, options = {}) {
    return request(url, { ...options, method: "DELETE" });
  },
};
