# DocumentDB Bootcamp (SEC #13)

Beginner-friendly starter for the Software Engineering Conversion Bootcamp.

This folder contains **two apps**:

| Folder | What it is | Default URL |
|---|---|---|
| `documentdb-backend/` | Laravel API (PHP) | http://localhost:8000 |
| `documentdb-frontend/` | React + Vite app | http://localhost:5173 |

Tonight's lesson focus: **React conditional UI by role**  
(Delete button is shown for `admin` / `manager`, hidden for `staff`.)

---

## 1. What you need installed

Install these **before** class if possible:

1. **Git**
2. **VS Code** (or Cursor)
3. **PHP 8.3+**
4. **Composer**
5. **Node.js 20+** (includes `npm`)
6. **PostgreSQL** (database)

### Quick checks (run in Terminal)

```bash
php -v
composer -V
node -v
npm -v
psql --version
```

If any command says `command not found`, install that tool first.

---

## 2. Get the project

```bash
cd ~/Codebase
# if you already have this repo cloned, just open it:
cd documentdb-bootcamp
```

Project layout:

```text
documentdb-bootcamp/
├── README.md                 ← you are here
├── .gitignore
├── documentdb-backend/       ← Laravel API
└── documentdb-frontend/      ← React app
```

---

## 3. Backend setup (Laravel + Postgres)

Open a terminal:

```bash
cd ~/Codebase/documentdb-bootcamp/documentdb-backend
```

### 3.1 Install PHP packages

```bash
composer install
```

### 3.2 Create your `.env` file

```bash
cp .env.example .env
php artisan key:generate
```

### 3.3 Configure Postgres in `.env`

Edit `documentdb-backend/.env` and set:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=document_db
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password
```

> If your Postgres uses another port (example: `5433`), change `DB_PORT`.

### 3.4 Create the database

In Postgres (psql, TablePlus, pgAdmin, etc.) create a database named:

```text
document_db
```

Example with `psql`:

```bash
psql -U postgres -c "CREATE DATABASE document_db;"
```

### 3.5 Run migrations + seed demo users

```bash
php artisan migrate --seed
```

This creates tables **and** demo accounts:

| Email | Password | Role |
|---|---|---|
| `admin@example.com` | `password` | admin |
| `manager@example.com` | `password` | manager |
| `staff@example.com` | `password` | staff |

### 3.6 Cloudflare R2 (file uploads)

File uploads use the `r2` disk. Ask your trainer for class credentials, then add them to `.env`:

```env
CLOUDFLARE_R2_ACCESS_KEY_ID=...
CLOUDFLARE_R2_SECRET_ACCESS_KEY=...
CLOUDFLARE_R2_BUCKET=...
CLOUDFLARE_R2_ENDPOINT=...
CLOUDFLARE_R2_URL=...
CLOUDFLARE_R2_USE_PATH_STYLE_ENDPOINT=false
```

If R2 is not ready yet, login + document listing still work; uploading a file will fail until R2 is configured.

### 3.7 Start the backend server

```bash
php artisan serve
```

Keep this terminal open. API should be at:

```text
http://localhost:8000
```

Health check:

```text
http://localhost:8000/up
```

---

## 4. Frontend setup (React + Vite)

Open a **second** terminal:

```bash
cd ~/Codebase/documentdb-bootcamp/documentdb-frontend
```

### 4.1 Install JS packages

```bash
npm install
```

### 4.2 Create frontend `.env`

```bash
cp .env.example .env
```

If `.env.example` is missing, create `.env` with:

```env
VITE_API_URL=http://localhost:8000
```

### 4.3 Start the frontend

```bash
npm run dev
```

Open the URL Vite prints (usually):

```text
http://localhost:5173
```

---

## 5. How to run both every day

You need **two terminals**:

**Terminal A — backend**

```bash
cd ~/Codebase/documentdb-bootcamp/documentdb-backend
php artisan serve
```

**Terminal B — frontend**

```bash
cd ~/Codebase/documentdb-bootcamp/documentdb-frontend
npm run dev
```

Then open http://localhost:5173

---

## 6. Try the role UI demo

1. Go to http://localhost:5173/login
2. Login as `staff@example.com` / `password`
3. Open Documents — you should **not** see Delete
4. Logout
5. Login as `admin@example.com` / `password`
6. Open Documents — you **should** see Delete

This is the React pattern for tonight:

```jsx
const canDelete = role === "admin" || role === "manager";

{canDelete && <button>Delete</button>}
```

---

## 7. Useful API endpoints

Base URL: `http://localhost:8000/api`

| Method | Path | Auth? | Purpose |
|---|---|---|---|
| POST | `/register` | no | create account (role = staff) |
| POST | `/login` | no | get token + user (includes roles) |
| GET | `/me` | yes | current user |
| POST | `/logout` | yes | revoke token |
| GET | `/documents` | yes | list documents |
| POST | `/documents` | yes | create document + upload file |
| GET | `/documents/{id}` | yes | show one document |
| PUT/PATCH | `/documents/{id}` | yes | update document |
| DELETE | `/documents/{id}` | yes | delete document |

Auth header example:

```text
Authorization: Bearer YOUR_TOKEN_HERE
Accept: application/json
```

---

## 8. Common beginner problems

### `SQLSTATE[08006] ... connection refused`
Postgres is not running, or `DB_HOST` / `DB_PORT` is wrong.

### `SQLSTATE[3D000] database "document_db" does not exist`
Create the database first (step 3.4).

### Frontend cannot login / CORS error
1. Backend must be running on port 8000
2. Frontend `.env` must have `VITE_API_URL=http://localhost:8000`
3. Restart `npm run dev` after changing `.env`

### `401 Unauthorized` on documents
You are not logged in, or token is missing. Login again.

### Upload fails with R2 / disk driver error
Your Cloudflare R2 `.env` values are missing or wrong. Ask trainer.

### `php artisan migrate` fails
Check `.env` DB values, then retry:

```bash
php artisan config:clear
php artisan migrate --seed
```

---

## 9. Git workflow (one repo for both apps)

This project is one Git repo at the root (`documentdb-bootcamp`).

```bash
cd ~/Codebase/documentdb-bootcamp
git status
```

Do **not** commit secrets:

- `documentdb-backend/.env`
- `documentdb-frontend/.env`

Those are already ignored.

---

## 10. What is intentionally simple

For this stage of class we keep things beginner-friendly:

- No Laravel Policies yet (that lesson comes later this week)
- Frontend role checks are UI-only demos (hide/show buttons)
- Controllers stay small and readable

Backend authorization with Policies/Gates will be taught separately.
