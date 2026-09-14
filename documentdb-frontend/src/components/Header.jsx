import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "../AuthContext";
import { apiUrl, authHeaders } from "../api";

function Header() {
  const { user, role, token, canUpload, logout } = useAuth();
  const navigate = useNavigate();

  async function handleLogout() {
    try {
      if (token) {
        await fetch(apiUrl("/logout"), {
          method: "POST",
          headers: authHeaders(token),
        });
      }
    } catch (error) {
      console.error(error);
    } finally {
      logout();
      navigate("/login");
    }
  }

  return (
    <nav className="bg-white shadow">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex h-16 items-center justify-between">
          <div className="flex items-center gap-6">
            <Link to="/" className="text-sm font-semibold text-gray-900">
              DocumentDB
            </Link>
            <Link to="/documents" className="text-sm text-gray-600 hover:text-gray-900">
              Documents
            </Link>
            {canUpload && (
              <Link
                to="/documents/create"
                className="text-sm text-gray-600 hover:text-gray-900"
              >
                Add Document
              </Link>
            )}
          </div>

          <div className="flex items-center gap-4 text-sm">
            {user ? (
              <>
                <span className="text-gray-700">
                  {user.name}
                  {role && (
                    <span className="ml-2 rounded bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                      {role}
                    </span>
                  )}
                </span>
                <button
                  type="button"
                  onClick={handleLogout}
                  className="text-gray-600 hover:text-gray-900"
                >
                  Logout
                </button>
              </>
            ) : (
              <Link to="/login" className="font-medium text-indigo-600 hover:text-indigo-500">
                Login
              </Link>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
}

export default Header;
