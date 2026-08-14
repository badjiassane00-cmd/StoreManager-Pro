📓 Journal de Développement (DEVLOG)
Nom & Prénom : Assane Badji
Projet : StoreManager Pro (ERP PHP/POO)  



1. Suivi Chronologique des Phases

 [Vendredi - Phase 1] :  Diagrammes Use Case & Classes dans /docs/.
- **Heure de réalisation** : 20H25 
Ce qui a été fait :
Analyse du fonctionnement général de StoreManager Pro.
Réalisation du diagramme de cas d'utilisation.
Réalisation du diagramme de classes.
Identification des principales entités du projet : Utilisateur, Produit, Client, Fournisseur, Commande, Dette et BonLivraison.
Identification des relations entre les différentes entités.
Difficultés / Obstacles :
J'ai eu des difficultés à comprendre les relations entre certaines classes.
J'ai dû revoir la différence entre une classe, un attribut et une relation.
J'ai vérifié que le diagramme de classes correspond bien aux fonctionnalités demandées.


 [Vendredi - Phase 1.2] :  Schéma SQL PostgreSQL / SQLite
- **Heure de réalisation** : 20H30 - 22H30 

Ce qui a été fait :
J’ai ajouté paiements_dettes et paiements_fournisseurs que j'avais hormis lors de mon diagramme de classe
Transformation du diagramme de classes en schéma relationnel.
Identification des tables nécessaires à la base de données.
Création des tables utilisateurs, produits, clients, fournisseurs, commandes, lignes_commandes, dettes, bons_livraison et lignes_bon_livraison.
Ajout des clés primaires.
Ajout des clés étrangères pour relier les tables.
Ajout des contraintes CHECK pour contrôler certaines valeurs.
Création du script PostgreSQL schema.sql.
Création du script SQLite schema_sqlite.sql.
Vérification de la cohérence des relations entre les tables.
**Difficultés / Obstacles** :
J'ai eu des difficultés à passer du PostgreSQL à SQLite.
J'ai d'abord créé des tables supplémentaires pour les paiements, puis j'ai constaté qu'elles ne correspondaient pas directement au diagramme de classes.
J'ai donc repris le schéma afin de rester fidèle au modèle UML.
J'ai également dû comprendre le rôle des clés étrangères et des contraintes CHECK.

 [Vendredi - Phase 1.3] :  Database Singleton & Fallback (Step 1.3)
- **Heure de réalisation** :22h00 - 23h00

Ce qui a été fait :
Création de la classe Database en tant que Singleton : elle garantit qu'une seule et unique instance de la classe (et donc une seule connexion PDO) existe pendant toute l'exécution du programme, au lieu d'ouvrir une nouvelle connexion à chaque fois qu'on a besoin de parler à la base de données.
Le constructeur __construct() est déclaré private. Concrètement, ça veut dire qu'il est interdit d'écrire new Database() n'importe où ailleurs dans le code (PHP refuserait avec une erreur). La seule façon d'obtenir l'objet est de passer par la méthode statique getInstance().
Deux propriétés private static ont été déclarées : $instance (qui contiendra l'unique objet Database) et $pdo (qui contiendra l'unique connexion PDO). Le mot-clé static signifie que ces propriétés appartiennent à la classe elle-même, pas à un objet en particulier : leur valeur est donc partagée par tout le programme, peu importe combien de fois on appelle Database::getInstance().

- **Difficultés / Obstacles** :
- 
  - Ayant commencé à apprendre la POO seulement hier, la notion de **Singleton** n'était pas évidente au début : comprendre pourquoi le constructeur doit être `private` (pour empêcher `new Database()` de l'extérieur) et pourquoi on utilise des propriétés `static` (pour qu'il n'existe qu'**une seule** instance et **une seule** connexion PDO partagée dans toute l'application) a demandé plusieurs relectures.
  - Le mécanisme `try/catch` pour le fallback PostgreSQL → SQLite était aussi nouveau : il a fallu comprendre qu'une `PDOException` est levée automatiquement par PHP quand la connexion échoue, et que le `catch` permet de "rattraper" cette erreur pour exécuter un plan B au lieu de faire planter le script.
  - Petite hésitation sur le diagramme de classes concernant les associations entre `Commande` et `Dette` (relation 0..1, une commande ne génère une dette que si elle est à crédit).

