import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { useAuth } from "../AuthContext";
import { apiUrl, authHeaders } from "../api";

function DocumentTable() {
  const { token, role, canUpload, canEdit, canDelete } = useAuth();
  const [documents, setDocuments] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchDocuments() {
      if (!token) {
        setLoading(false);
        return;
      }

      try {
        const response = await fetch(apiUrl("/documents"), {
          headers: authHeaders(token),
        });
        const data = await response.json();
        setDocuments(data.data ?? []);
      } catch (error) {
        console.log(error);
      } finally {
        setLoading(false);
      }
    }

    fetchDocuments();
  }, [token]);

  async function handleDelete(id) {
    if (!canDelete) {
      return;
    }

    const confirmed = window.confirm("Delete this document?");
    if (!confirmed) {
      return;
    }

    try {
      const response = await fetch(apiUrl(`/documents/${id}`), {
        method: "DELETE",
        headers: authHeaders(token),
      });

      if (response.ok) {
        setDocuments((current) => current.filter((doc) => doc.id !== id));
      }
    } catch (error) {
      console.error(error);
    }
  }

  if (!token) {
    return (
      <div className="max-w-7xl mx-auto m-20 px-4 text-sm text-gray-700">
        Please <Link className="text-indigo-600" to="/login">login</Link> to view documents.
      </div>
    );
  }

  if (loading) {
    return <div className="max-w-7xl mx-auto m-20 px-4 text-sm text-gray-500">Loading...</div>;
  }

  const showActions = canEdit || canDelete;

  return (
    <div className="max-w-7xl px-4 sm:px-6 lg:px-8 mx-auto m-20">
      <div className="sm:flex sm:items-center">
        <div className="sm:flex-auto">
          <h1 className="text-base font-semibold text-gray-900">Documents</h1>
          <p className="mt-2 text-sm text-gray-700">
            Your role: <strong>{role ?? "unknown"}</strong>
            {role === "admin" && " — you can upload, edit, and delete."}
            {role === "manager" && " — you can upload and view."}
            {role === "staff" && " — view only."}
          </p>
        </div>
        {canUpload && (
          <div className="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <Link
              to="/documents/create"
              className="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
            >
              Add Document
            </Link>
          </div>
        )}
      </div>

      <div className="mt-8 flow-root">
        <div className="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div className="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <table className="min-w-full divide-y divide-gray-300">
              <thead>
                <tr className="divide-x divide-gray-200">
                  <th className="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                    Title
                  </th>
                  <th className="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">
                    Description
                  </th>
                  <th className="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">
                    Created At
                  </th>
                  <th className="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">
                    File
                  </th>
                  {showActions && (
                    <th className="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">
                      Actions
                    </th>
                  )}
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 bg-white">
                {documents.map((document) => (
                  <tr key={document.id} className="divide-x divide-gray-200">
                    <td className="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-0">
                      {document.title}
                    </td>
                    <td className="whitespace-nowrap p-4 text-sm text-gray-500">
                      {document.description}
                    </td>
                    <td className="whitespace-nowrap p-4 text-sm text-gray-500">
                      {document.created_at}
                    </td>
                    <td className="whitespace-nowrap p-4 text-sm text-gray-500">
                      {document.file_url ? (
                        <a
                          href={document.file_url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="font-medium text-indigo-600 hover:text-indigo-500"
                        >
                          View file
                        </a>
                      ) : (
                        <span className="text-gray-400">No file</span>
                      )}
                    </td>
                    {showActions && (
                      <td className="whitespace-nowrap p-4 text-sm space-x-3">
                        {canEdit && (
                          <Link
                            to={`/documents/${document.id}/edit`}
                            className="font-medium text-indigo-600 hover:text-indigo-500"
                          >
                            Edit
                          </Link>
                        )}
                        {canDelete && (
                          <button
                            type="button"
                            onClick={() => handleDelete(document.id)}
                            className="font-medium text-red-600 hover:text-red-500"
                          >
                            Delete
                          </button>
                        )}
                      </td>
                    )}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
}

export default DocumentTable;
