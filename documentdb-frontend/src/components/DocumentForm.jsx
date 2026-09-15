import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { useAuth } from "../AuthContext";
import { apiUrl, authHeaders } from "../api";

function DocumentForm() {
  const navigate = useNavigate();
  const { id } = useParams();
  const isEdit = Boolean(id);
  const { token, canUpload, canEdit } = useAuth();
  const [loading, setLoading] = useState(false);
  const [loadingDocument, setLoadingDocument] = useState(isEdit);
  const [formData, setFormData] = useState({
    title: "",
    description: "",
    category_id: "",
    file: null,
  });
  const [categories, setCategories] = useState([]);

  const allowed = isEdit ? canEdit : canUpload;

  useEffect(() => {
    async function fetchCategories() {
      if (!token) {
        return;
      }

      try {
        const response = await fetch(apiUrl("/categories"), {
          headers: authHeaders(token),
        });
        const data = await response.json();
        setCategories(data.data ?? []);
      } catch (error) {
        console.error(error);
      }
    }

    fetchCategories();
  }, [token]);

  useEffect(() => {
    async function loadDocument() {
      if (!isEdit || !token) {
        setLoadingDocument(false);
        return;
      }

      try {
        const response = await fetch(apiUrl(`/documents/${id}`), {
          headers: authHeaders(token),
        });
        const payload = await response.json();
        const document = payload.data ?? payload;

        setFormData({
          title: document.title ?? "",
          description: document.description ?? "",
          category_id: document.category?.id ? String(document.category.id) : "",
          file: null,
        });
      } catch (error) {
        console.error(error);
      } finally {
        setLoadingDocument(false);
      }
    }

    loadDocument();
  }, [id, isEdit, token]);

  if (!token) {
    return (
      <div className="max-w-3xl mx-auto m-20 p-6 text-sm text-gray-700">
        Please login before {isEdit ? "editing" : "creating"} a document.
      </div>
    );
  }

  if (!allowed) {
    return (
      <div className="max-w-3xl mx-auto m-20 p-6 text-sm text-gray-700">
        Your role cannot {isEdit ? "edit" : "upload"} documents.
      </div>
    );
  }

  if (loadingDocument) {
    return (
      <div className="max-w-3xl mx-auto m-20 p-6 text-sm text-gray-500">Loading...</div>
    );
  }

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData({ ...formData, [name]: value });
  };

  const handleFileChange = (e) => {
    setFormData({ ...formData, file: e.target.files[0] });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    const data = new FormData();
    data.append("title", formData.title);
    data.append("description", formData.description);
    data.append("category_id", formData.category_id);
    if (formData.file) {
      data.append("document", formData.file);
    }
    if (isEdit) {
      data.append("_method", "PUT");
    }

    try {
      const response = await fetch(
        apiUrl(isEdit ? `/documents/${id}` : "/documents"),
        {
          method: "POST",
          body: data,
          headers: authHeaders(token),
        }
      );

      if (response.ok) {
        navigate("/documents");
      } else {
        console.error(isEdit ? "Failed to update document" : "Failed to create document");
      }
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-3xl mx-auto m-20 p-6 bg-white shadow sm:rounded-lg">
      <h1 className="text-xl font-semibold text-gray-900 mb-6">
        {isEdit ? "Edit Document" : "Create New Document"}
      </h1>

      <form onSubmit={handleSubmit} className="space-y-6">
        <div>
          <label className="block text-sm font-medium text-gray-700">Title</label>
          <input
            type="text"
            name="title"
            required
            value={formData.title}
            onChange={handleInputChange}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700">Description</label>
          <textarea
            name="description"
            rows={3}
            value={formData.description}
            onChange={handleInputChange}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700">Category</label>
          <select
            name="category_id"
            value={formData.category_id}
            onChange={handleInputChange}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"
          >
            <option value="">No category</option>
            {categories.map((category) => (
              <option key={category.id} value={category.id}>
                {category.name}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700">
            Document File{isEdit ? " (optional)" : ""}
          </label>
          <input
            type="file"
            name="file"
            required={!isEdit}
            onChange={handleFileChange}
            className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
          />
        </div>

        <div className="flex justify-end gap-3">
          <button
            type="button"
            onClick={() => navigate("/documents")}
            className="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            disabled={loading}
            className="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:bg-indigo-400"
          >
            {loading ? "Saving..." : isEdit ? "Update Document" : "Save Document"}
          </button>
        </div>
      </form>
    </div>
  );
}

export default DocumentForm;
