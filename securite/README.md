# 🚨 RMC BTP SARL - Application Sécurité & Pointage du Personnel (Gardien)

Application web mobile-first conçue pour le poste de sécurité / gardiennage des chantiers et du siège de **RMC BTP SARL** (accessible sur le sous-domaine `security.rmcbtpsarl.com`).

---

## 🎯 Objectifs & Rôles

1. **Recensement du Personnel (`personnel.php`)**
   - Enregistrement des ouvriers, techniciens et cadres (Matricule, Nom, Téléphone, Métier/Fonction, Département, Heure d'embauche normale).
   - Gestion de l'effectif actif et inactif.

2. **Pointage Tactile en 1 Clic (`index.php`)**
   - **Arrivée en 1 Clic** : Le gardien clique sur `🟢 Pointer Arrivée (08:02)`. L'horodatage est automatique.
   - **Détection des Retards** : Alerte visuelle immédiate si l'arrivée dépasse l'heure normale.
   - **Départ en 1 Clic** : Le gardien clique sur `🔴 Pointer Départ (17:05)`. Le temps passé sur site est automatiquement calculé.
   - **Compteurs en direct** : Présents sur site, déjà sortis, absents, retards.

3. **Génération & Envoi Automatique de Rapports pour le Boss (`rapports.php`)**
   - **Rapport Journalier** : Fiche d'émargement officielle avec taux de présence et liste des absents.
   - **Rapport Hebdomadaire** : Volume horaire presté par ouvrier pour la semaine.
   - **Rapport Mensuel (Paie)** : Bilan mensuel complet du personnel pour le service comptable et la paie.
   - **📱 Envoi WhatsApp Direct au Boss** : Bouton 1-clic générant un message pré-formaté professionnel transmis directement sur le WhatsApp de la Direction Générale.
   - **🖨️ Fiches Prêtes à Imprimer** avec zones de visa pour le chef de poste sécurité et le Directeur Général.

4. **Historique & Export (`historique.php`)**
   - Consultation des archives de pointage et export Excel / CSV.

---

## 🔐 Identifiants par défaut

- **Gardien** : `gardien` / `gardien123`
- **Boss (Direction)** : `boss` / `admin123`

---

## 🛠️ Configuration Technique

- **Sous-domaine** : `security.rmcbtpsarl.com`
- **Moteur Base de Données** : Dual MySQL (`securite_db`) avec fallback automatique SQLite (`database.sqlite`).
- **PHP** : Compatible PHP 7.4, 8.0, 8.1, 8.2+.
