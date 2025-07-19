# Laravel 12 Turn-Key Starter

🚀 **Start your Laravel 12 project instantly, no setup required!**

This is a ready-to-use Laravel 12 distribution designed to get you up and running immediately, with pre-configured tools and environments tailored for:

- **Windows** (using Laragon)
- **Linux** (optimized for VPS deployment via Plesk)
- **Steam Deck** (powered by Larasail for local development)

## ✨ Features

- **Livewire + Volt** pre-installed and configured
- **Larasail** for seamless local development on Steam Deck
- **MaryUI** as the frontend library: basic layout provided -> headline, navbar, sidebar, main content => responsive!
- **Vite** as the asset compiler
- **Custom Error Pages** (401, 403, 404, 405, 408, 419, 500, 503) pre-configured
- **Basic Routing & Authentication** (login & register pages, forgotten password workflow, no starter kit)
- **Atomic Deployment Script** for near-zero downtime deployments

## 🚀 Getting Started

### 1️⃣ Use as a Template
Create a new repository from this template and clone it locally.

### 2️⃣ Initial Setup
After cloning your new repo:

1. Create a `.env` file and configure it to match your project needs (database, email, logs, storage...)
2. Adjust `composer.json` if necessary
3. Run:
   ```bash
   php artisan key:generate
   ```
4. If needed, configure storage symlink:
   - **Windows**: Inside `/public` run:
     ```cmd
     mklink /D storage ..\storage\app\public
     ```
   - **Linux**:
     ```bash
     php artisan storage:link
     ```

### 3️⃣ Deploy with Zero-Downtime
Run the atomic deployment script in your terminal:
```bash
bash atomic-deploy.sh
```
(Supported in **Cmder** or **VSCode Terminal** on Windows)

---

✅ **Ready to build something awesome with Laravel 12? Get started now!** 🎉

EDIT: Added IVAO Branding + OAuth
.env file requires:
```
IVAO_CLIENT_ID=
IVAO_CLIENT_SECRET=
OPENID_URL=https://api.ivao.aero/.well-known/openid-configuration
IVAO_SSO_SUCCESS_ROUTE_NAME=hello
```
