const API_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

export function apiUrl(path) {
  return `${API_URL}/api${path}`;
}

export function authHeaders(token, extra = {}) {
  return {
    Accept: "application/json",
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...extra,
  };
}
