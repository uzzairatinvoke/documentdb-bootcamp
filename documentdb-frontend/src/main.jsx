import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import "./App.css";
import { AuthProvider } from "./AuthContext";
import Header from "./components/Header.jsx";
import Home from "./components/Home.jsx";
import DocumentTable from "./components/DocumentTable.jsx";
import DocumentForm from "./components/DocumentForm.jsx";
import Login from "./components/Login.jsx";

createRoot(document.getElementById("root")).render(
  <StrictMode>
    <BrowserRouter>
      <AuthProvider>
        <Header />
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/login" element={<Login />} />
          <Route path="/documents" element={<DocumentTable />} />
          <Route path="/documents/create" element={<DocumentForm />} />
          <Route path="/documents/:id/edit" element={<DocumentForm />} />
        </Routes>
      </AuthProvider>
    </BrowserRouter>
  </StrictMode>
);
