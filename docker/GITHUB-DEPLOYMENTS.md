# GitHub Deployments API Integration für Coolify

Diese Integration aktualisiert automatisch den GitHub Deployment-Status, wenn dein Container startet.

## 🚀 Setup in Coolify

### 1. GitHub Personal Access Token erstellen

1. Gehe zu GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Klicke auf "Generate new token (classic)"
3. Gib dem Token einen Namen (z.B. "Coolify Deployments")
4. Wähle die folgenden Scopes aus:
   - ✅ `repo` (Full control of private repositories)
   - ✅ `repo_deployment` (wird automatisch mit `repo` aktiviert)
5. Generiere den Token und kopiere ihn (du siehst ihn nur einmal!)

### 2. Environment Variables in Coolify setzen

Gehe zu deinem Coolify-Projekt → Environment Variables und füge folgende Variablen hinzu:

#### Erforderliche Variablen:

```bash
# GitHub Personal Access Token (als Secret markieren!)
GITHUB_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

# Repository im Format "owner/repo"
GITHUB_REPO=deinusername/deinrepo
```

#### Optionale Variablen (mit Standardwerten):

```bash
# Branch oder Tag (Standard: "main")
GITHUB_REF=main

# Deployment Environment (Standard: "production")
# Wichtig: "production" oder "prod" = production_environment: true
#          Alles andere (z.B. "staging", "preview") = transient_environment: true
DEPLOYMENT_ENVIRONMENT=production

# Die öffentliche URL deiner App
# Coolify setzt oft automatisch APP_URL, falls nicht:
APP_URL=https://deine-app.example.com

# Optional: Separate Log-URL (Standard: gleich wie APP_URL)
# Nutzt GitHub's neuen log_url Parameter statt deprecated target_url
DEPLOYMENT_LOG_URL=https://deine-app.example.com/logs
```

### 3. Dynamische Branch-Erkennung (optional)

Wenn Coolify die `GIT_BRANCH` Variable setzt, kannst du sie nutzen:

```bash
# In Coolify, falls verfügbar:
GITHUB_REF=${GIT_BRANCH}
```

Oder in deinem Dockerfile:
```dockerfile
ARG GIT_BRANCH=main
ENV GITHUB_REF=${GIT_BRANCH}
```

## 🔍 Verifikation

Nach dem Deployment:

1. Schaue in die Container-Logs in Coolify
2. Du solltest folgende Ausgabe sehen:
   ```
   Starting GitHub Deployment status update...
   📦 Repository: username/repo
   🌿 Ref: main
   🌍 Environment: production
   🔗 URL: https://deine-app.example.com
   Creating GitHub deployment...
   ✅ Deployment created with ID: 123456789
   Setting deployment status to success...
   ✅ Deployment status updated to success
   GitHub Deployment update completed
   ```

3. Gehe zu deinem GitHub Repository → Deployments
4. Du solltest dort den aktuellen Deployment-Status sehen

## ⚠️ Troubleshooting

### "GitHub Deployment update skipped: GITHUB_TOKEN or GITHUB_REPO not set"

- Stelle sicher, dass `GITHUB_TOKEN` und `GITHUB_REPO` in Coolify gesetzt sind
- Überprüfe, dass die Variablen nicht leer sind

### "Failed to create deployment"

Mögliche Ursachen:
- Token-Berechtigungen fehlen (benötigt `repo` scope)
- Repository existiert nicht oder Token hat keinen Zugriff
- GitHub API Rate Limit erreicht
- Netzwerkprobleme

### Script blockiert Container-Start nicht

Das Script ist so designed, dass es **niemals** den Container-Start blockiert:
- Alle Fehler werden mit `|| true` oder `exit 0` abgefangen
- API-Calls haben ein 10-Sekunden Timeout
- Fehlende Variablen führen zu einem Skip, nicht zu einem Fehler

## 🎨 Verschiedene Environments

Du kannst mehrere Coolify-Projekte mit unterschiedlichen Environments haben:

### Production:
```bash
DEPLOYMENT_ENVIRONMENT=production
GITHUB_REF=main
```

### Staging:
```bash
DEPLOYMENT_ENVIRONMENT=staging
GITHUB_REF=develop
```

### Preview/Feature:
```bash
DEPLOYMENT_ENVIRONMENT=preview
GITHUB_REF=feature/neue-funktion
```

## 📊 GitHub Deployment View

Nach erfolgreichen Deployments siehst du in GitHub:

- **Environments Tab**: Liste aller Environments (production, staging, etc.)
- **Deployments**: Historie aller Deployments
- **Status**: Success/Failure für jedes Deployment
- **Environment URL**: Direktlink zur deployed App

## 🔐 Security Best Practices

1. **Markiere `GITHUB_TOKEN` als Secret** in Coolify
2. Verwende einen Token mit **minimalen Berechtigungen**
3. Nutze verschiedene Tokens für verschiedene Repositories
4. Rotiere Tokens regelmäßig
5. Deaktiviere Tokens, die nicht mehr benötigt werden

## 🛠️ Technische Details

### Script-Ausführung

Das Script wird automatisch ausgeführt:
- **Wann**: Beim Container-Start, vor der Hauptanwendung
- **Wo**: `/etc/entrypoint.d/10-github-deployment.sh`
- **Reihenfolge**: Nummer `10` = früh im Startup-Prozess
- **Laufzeit**: ~1-2 Sekunden (oder sofort bei Skip)

### GitHub API Konformität (2022-11-28)

Das Script nutzt die aktuellsten GitHub API Best Practices:

- ✅ **`log_url`** statt deprecated `target_url`
- ✅ **`production_environment`** explizit gesetzt basierend auf Environment-Name
- ✅ **`transient_environment`** für staging/preview (zeigt Deployments als "destroyed" wenn inactive)
- ✅ **Manuelle Deaktivierung** alter Deployments (da `auto_inactive` nur für non-production funktioniert)
- ✅ **Explizite `environment`** Parameter um Ambiguität zu vermeiden

### API Endpoints

Das Script verwendet die GitHub Deployments API v3:

1. **POST** `/repos/{owner}/{repo}/deployments` - Erstellt Deployment
2. **POST** `/repos/{owner}/{repo}/deployments/{deployment_id}/statuses` - Setzt Status

### Fehlerbehandlung

- ✅ Script beendet sich immer mit Exit Code 0
- ✅ API-Calls haben 10s Timeout
- ✅ Fehlende Credentials führen zu Skip
- ✅ API-Fehler werden geloggt aber nicht geworfen
- ✅ Container startet **immer**, auch bei Fehlern

## 📚 Weitere Informationen

- [GitHub Deployments API Docs](https://docs.github.com/de/rest/deployments/deployments?apiVersion=2022-11-28)
- [GitHub Deployment Statuses](https://docs.github.com/de/rest/deployments/statuses)
- [Coolify Documentation](https://coolify.io/docs)
