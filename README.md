# 🎁 ReclaimSystem

![Version](https://img.shields.io/badge/version-1.0.0-blue)  
![Platform](https://img.shields.io/badge/platform-PocketMine--MP-yellow)  
![API](https://img.shields.io/badge/api-5.0.0-blue)  

---

## 📝 Description

**ReclaimSystem** is a customizable PocketMine-MP plugin that allows players to claim predefined item rewards with cooldowns and permission restrictions.

Features include:

- ⚙️ Multiple reclaim types with independent cooldowns and permission requirements.  
- 🛠️ Admin commands to create, edit, and delete reclaims.  
- 📦 GUI editor for easy rewards management.  
- ⏳ Cooldown system to prevent abuse.  
- 🔐 Permission system to restrict access to specific reclaims.

---

## 🚀 Features

- 🎁 Players can claim configured rewards with `/reclaim`.  
- ⏲️ Each reclaim has a cooldown to control how often it can be claimed.  
- 🔑 Permissions control who can access each reclaim.  
- 🛠️ Admin commands:  
  - `/reclaim create <name>` — Creates a new reclaim with default settings.  
  - `/reclaim delete <name>` — Deletes an existing reclaim.  
  - `/reclaim edit <name> cooldown <time>` — Sets cooldown for a reclaim (supports formats like 1h, 30m).  
  - `/reclaim edit <name> permission <permission|none>` — Sets or removes permission required.  
  - `/reclaim edit <name> rewards` — Opens the GUI editor to add/remove items in the reclaim.  
  - `/reclaim resetcooldown <player> <reclaim>` — Resets cooldown for a player on a specific reclaim.  
  - `/reclaim list` — Lists all configured reclaims.

---

## 🔐 Permissions

| Permission                     | Description                                |
|-------------------------------|--------------------------------------------|
| `reclaim.command.permission`  | Allows using the `/reclaim` command.       |
| `reclaim.admin.command.permission` | Allows access to admin reclaim commands. |

---

## ⚙️ Configuration

- Reclaim data is stored per reclaim in individual YAML files inside the plugin’s `reclaims` folder.  
- Cooldowns per player are saved in a `cooldowns.yml` file inside the plugin data folder.  
- No extra config needed; all settings are managed via commands and GUI.

---

## 📖 Usage

- Players use `/reclaim` to get their available rewards if cooldowns permit.  
- Admins manage reclaims with `/reclaim create`, `/reclaim edit`, `/reclaim delete`, etc.  
- Editing rewards opens a GUI where you can add or remove items for the reclaim.

---