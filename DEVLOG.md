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
Difficultés / Obstacles :
J'ai eu des difficultés à passer du PostgreSQL à SQLite.
J'ai d'abord créé des tables supplémentaires pour les paiements, puis j'ai constaté qu'elles ne correspondaient pas directement au diagramme de classes.
J'ai donc repris le schéma afin de rester fidèle au modèle UML.
J'ai également dû comprendre le rôle des clés étrangères et des contraintes CHECK.
