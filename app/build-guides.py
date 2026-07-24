#!/usr/bin/env python3
# Resynchronise le contenu des fiches de guides.js depuis les fichiers .md sources.
# guides.js = const GUIDES={...JSON...}; -> on relit le JSON, on remplace le "md"
# des cles mappees par le contenu du .md correspondant, on reecrit.
# La cle "apercu" (sans fichier source) et tous les titres "t" sont preserves.
import json, os

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))  # .../Compostel
GUIDES_JS = os.path.join(ROOT, "app", "guides.js")

# cle de fiche -> fichier markdown source
MAP = {
    "sac":       "sac/inventaire.md",
    "checklist": "sac/check-avant-depart.md",
    "forme":     "prep/forme-physique.md",
    "papiers":   "prep/credencial-et-papiers.md",
    "sante":     "prep/sante-pieds-pharmacie.md",
    "meteo":     "prep/meteo-saison.md",
    "budget":    "prep/budget.md",
    "faune":     "prep/faune-dangereuse.md",
}

src = open(GUIDES_JS, encoding="utf-8").read()
i, j = src.index("{"), src.rindex("}")
prefix = src[:i]                      # "/* ... */\nconst GUIDES="
data = json.loads(src[i:j+1])

changed = []
for key, rel in MAP.items():
    path = os.path.join(ROOT, rel)
    if key in data and os.path.exists(path):
        md = open(path, encoding="utf-8").read()
        if data[key].get("md") != md:
            data[key]["md"] = md
            changed.append(key)

out = prefix + json.dumps(data, ensure_ascii=False) + ";\n"
open(GUIDES_JS, "w", encoding="utf-8", newline="\n").write(out)
print("guides.js resynchronise. Fiches mises a jour:", ", ".join(changed) if changed else "(aucune)")
