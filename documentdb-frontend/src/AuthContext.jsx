import { createContext, useContext, useState } from "react";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  // token
  const [token, setToken] = useState(() => localStorage.getItem("token"));
  // user object
  const [user, setUser] = useState(() => {
    const saved = localStorage.getItem("user");
    return saved ? JSON.parse(saved) : null;
  });

  function login(nextToken, nextUser) {
    localStorage.setItem("token", nextToken);
    localStorage.setItem("user", JSON.stringify(nextUser));
    setToken(nextToken);
    setUser(nextUser);
  }

  function logout() {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    setToken(null);
    setUser(null);
  }

  // Slide matrix: admin = full, manager = upload+view, staff = view only
  const role = user?.roles?.[0] ?? null;
  const canUpload = role === "admin" || role === "manager";
  const canEdit = role === "admin";
  const canDelete = role === "admin";

  return (
    <AuthContext.Provider
      value={{ token, user, role, canUpload, canEdit, canDelete, login, logout }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}
