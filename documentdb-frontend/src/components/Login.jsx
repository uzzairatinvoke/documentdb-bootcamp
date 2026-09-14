import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../AuthContext";
import { apiUrl, authHeaders } from "../api";

function Login() {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [email, setEmail] = useState("staff@example.com");
  const [password, setPassword] = useState("password");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e) {
    e.preventDefault();
    setLoading(true);
    setError("");

    try {
      const response = await fetch(apiUrl("/login"), {
        method: "POST",
        headers: authHeaders(null, { "Content-Type": "application/json" }),
        body: JSON.stringify({ email, password }),
      });

      const data = await response.json();

      if (!response.ok) {
        setError(data.message || "Login failed");
        return;
      }

      login(data.token, data.user);
      navigate("/documents");
    } catch (err) {
      console.error(err);
      setError("Could not reach the API. Is the backend running?");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="max-w-md mx-auto m-20 p-6 bg-white shadow sm:rounded-lg">
      <h1 className="text-xl font-semibold text-gray-900 mb-2">Login</h1>
      <p className="text-sm text-gray-600 mb-6">
        Demo accounts (password: <code>password</code>):
        <br />
        admin@example.com · manager@example.com · staff@example.com
      </p>

      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label className="block text-sm font-medium text-gray-700">Email</label>
          <input
            type="email"
            required
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="mt-1 block w-full rounded-md border border-gray-300 p-2 sm:text-sm"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700">Password</label>
          <input
            type="password"
            required
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="mt-1 block w-full rounded-md border border-gray-300 p-2 sm:text-sm"
          />
        </div>

        {error && <p className="text-sm text-red-600">{error}</p>}

        <button
          type="submit"
          disabled={loading}
          className="w-full rounded-md bg-indigo-600 py-2 px-4 text-sm font-medium text-white hover:bg-indigo-700 disabled:bg-indigo-400"
        >
          {loading ? "Signing in..." : "Sign in"}
        </button>
      </form>
    </div>
  );
}

export default Login;
