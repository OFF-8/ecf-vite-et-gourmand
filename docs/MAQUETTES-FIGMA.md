# Maquettes Figma — guide ECF

## Livrables attendus

| Document | Format | Contenu |
|----------|--------|---------|
| Charte graphique | **PDF** | Palette, polices, composants |
| Maquettes bureautiques | **PDF ou PNG** | 3 pages × (wireframe + mockup) |
| Maquettes mobiles | **PDF ou PNG** | 3 pages × (wireframe + mockup) |

---

## Charte graphique → PDF

1. Ouvre `docs/charte-graphique.html` dans Chrome ou Edge
2. **Ctrl + P** → Destination : **Enregistrer au format PDF**
3. Enregistre sous : `charte-graphique-vite-et-gourmand.pdf`

---

## Les 3 pages à maquetter

Reproduis ces écrans depuis ton site local (`http://localhost/ecf/`) :

### 1. Accueil (`index.php`)
- Hero bordeaux + titre + 2 boutons
- 3 cartes « Pourquoi nous choisir »
- 3 cartes « Menus phares »
- Bandeau contact

### 2. Liste des menus (`menus.php`)
- Filtres (prix, thème, régime)
- Grille de cartes menus

### 3. Détail menu (`menu-detail.php?id=1`)
- Galerie, description, plats, bouton commander

*(Alternative acceptable pour la 3ᵉ : **Connexion** ou **Formulaire commande**)*

---

## Frames Figma recommandées

| Frame | Taille |
|-------|--------|
| Desktop — Wireframe | 1440 × 1024 px |
| Desktop — Mockup | 1440 × 1024 px |
| Mobile — Wireframe | 390 × 844 px |
| Mobile — Mockup | 390 × 844 px |

**Total : 12 frames** (6 wireframes + 6 mockups)  
ou **6 exports** si tu regroupes wireframe + mockup par page.

---

## Wireframe vs Mockup

### Wireframe (fil de fer)
- Couleurs : blanc, gris clair `#E8E0D8`, gris `#6C757D`
- Pas de photos — rectangles gris à la place des images
- Texte : « Titre », « Lorem ipsum », barres grises
- But : montrer la **structure** et la **navigation**

### Mockup (maquette haute fidélité)
- Appliquer la charte :
  - Bordeaux foncé `#4A1F2B`
  - Bordeaux `#6B2C3E`
  - Doré `#C9A227`
  - Crème `#FAF6F0`
- Police titres : **Cormorant Garamond**
- Police texte : **Segoe UI** ou **Inter**
- Vrais textes du site (menus, prix, boutons)

---

## Export depuis Figma

1. Sélectionne la frame
2. Panneau droit → **Export**
3. Format : **PDF** (pour le dossier) ou **PNG @2x** (pour slides)
4. Nommage conseillé :
   - `01-accueil-desktop-wireframe.pdf`
   - `01-accueil-desktop-mockup.pdf`
   - `01-accueil-mobile-wireframe.pdf`
   - etc.

---

## Checklist avant remise

- [ ] PDF charte graphique (palette + polices)
- [ ] 3 wireframes desktop
- [ ] 3 mockups desktop
- [ ] 3 wireframes mobile
- [ ] 3 mockups mobile
- [ ] Cohérence avec le site PHP en ligne / local
