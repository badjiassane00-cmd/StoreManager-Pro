-- StoreManager Pro - PostgreSQL
-- Phase 1.2 : Schéma SQL



CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(30) NOT NULL UNIQUE,
    CONSTRAINT ck_roles_nom CHECK (nom IN ('ADMIN', 'VENTE', 'STOCK', 'INVENTAIRE'))
);

CREATE TABLE utilisateurs (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role_id INT NOT NULL REFERENCES roles(id),
    actif BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE produits (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(120) NOT NULL UNIQUE,
    prix_unitaire NUMERIC(12,2) NOT NULL,
    quantite_stock INT NOT NULL DEFAULT 0,
    seuil_alerte INT NOT NULL DEFAULT 5,
    CONSTRAINT ck_produits_prix CHECK (prix_unitaire >= 0),
    CONSTRAINT ck_produits_stock CHECK (quantite_stock >= 0),
    CONSTRAINT ck_produits_seuil CHECK (seuil_alerte >= 0)
);

CREATE TABLE clients (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    telephone VARCHAR(30),
    email VARCHAR(120),
    limite_credit NUMERIC(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT ck_clients_limite_credit CHECK (limite_credit >= 0)
);

CREATE TABLE fournisseurs (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(120) NOT NULL UNIQUE,
    telephone VARCHAR(30),
    adresse VARCHAR(255),
    email VARCHAR(120)
);

CREATE TABLE commandes (
    id SERIAL PRIMARY KEY,
    date_vente TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    client_id INT REFERENCES clients(id) ON DELETE SET NULL,
    utilisateur_id INT NOT NULL REFERENCES utilisateurs(id),
    montant_total NUMERIC(12,2) NOT NULL DEFAULT 0,
    montant_verse NUMERIC(12,2) NOT NULL DEFAULT 0,
    statut_paiement VARCHAR(20) NOT NULL DEFAULT 'COMPTANT',
    CONSTRAINT ck_commandes_statut CHECK (statut_paiement IN ('COMPTANT', 'CREDIT')),
    CONSTRAINT ck_commandes_total CHECK (montant_total >= 0),
    CONSTRAINT ck_commandes_verse CHECK (montant_verse >= 0),
    CONSTRAINT ck_commandes_verse_total CHECK (montant_verse <= montant_total)
);

CREATE TABLE lignes_commandes (
    id SERIAL PRIMARY KEY,
    commande_id INT NOT NULL REFERENCES commandes(id) ON DELETE CASCADE,
    produit_id INT NOT NULL REFERENCES produits(id),
    quantite INT NOT NULL,
    prix_unitaire NUMERIC(12,2) NOT NULL,
    CONSTRAINT ck_lignes_commandes_qte CHECK (quantite > 0),
    CONSTRAINT ck_lignes_commandes_prix CHECK (prix_unitaire >= 0),
    CONSTRAINT uq_lignes_commandes UNIQUE (commande_id, produit_id)
);

CREATE TABLE dettes (
    id SERIAL PRIMARY KEY,
    commande_id INT NOT NULL UNIQUE REFERENCES commandes(id) ON DELETE CASCADE,
    client_id INT NOT NULL REFERENCES clients(id),
    montant_initial NUMERIC(12,2) NOT NULL,
    montant_paye NUMERIC(12,2) NOT NULL DEFAULT 0,
    statut VARCHAR(20) NOT NULL DEFAULT 'NON_SOLDEE',
    CONSTRAINT ck_dettes_montant_initial CHECK (montant_initial > 0),
    CONSTRAINT ck_dettes_montant_paye CHECK (montant_paye >= 0),
    CONSTRAINT ck_dettes_paye_initial CHECK (montant_paye <= montant_initial),
    CONSTRAINT ck_dettes_statut CHECK (statut IN ('NON_SOLDEE', 'PARTIELLE', 'SOLDEE'))
);

CREATE TABLE paiements_dettes (
    id SERIAL PRIMARY KEY,
    dette_id INT NOT NULL REFERENCES dettes(id) ON DELETE CASCADE,
    montant NUMERIC(12,2) NOT NULL,
    date_paiement TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mode_paiement VARCHAR(20) NOT NULL DEFAULT 'ESPECES',
    CONSTRAINT ck_paiements_dettes_montant CHECK (montant > 0),
    CONSTRAINT ck_paiements_dettes_mode CHECK (mode_paiement IN ('ESPECES', 'WAVE', 'OM', 'CARTE', 'AUTRE'))
);

CREATE TABLE bons_livraison (
    id SERIAL PRIMARY KEY,
    fournisseur_id INT NOT NULL REFERENCES fournisseurs(id),
    utilisateur_id INT NOT NULL REFERENCES utilisateurs(id),
    date_reception TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    montant_facture NUMERIC(12,2) NOT NULL DEFAULT 0,
    statut_reglement VARCHAR(20) NOT NULL DEFAULT 'IMPAYEE',
    CONSTRAINT ck_bl_montant CHECK (montant_facture >= 0),
    CONSTRAINT ck_bl_statut CHECK (statut_reglement IN ('PAYEE', 'IMPAYEE'))
);

CREATE TABLE lignes_bons_livraison (
    id SERIAL PRIMARY KEY,
    bon_livraison_id INT NOT NULL REFERENCES bons_livraison(id) ON DELETE CASCADE,
    produit_id INT NOT NULL REFERENCES produits(id),
    quantite_livree INT NOT NULL DEFAULT 0,
    cout_achat NUMERIC(12,2) NOT NULL,
    CONSTRAINT ck_lignes_bl_qte CHECK (quantite_livree >= 0),
    CONSTRAINT ck_lignes_bl_cout CHECK (cout_achat >= 0),
    CONSTRAINT uq_lignes_bl UNIQUE (bon_livraison_id, produit_id)
);

CREATE TABLE paiements_fournisseurs (
    id SERIAL PRIMARY KEY,
    fournisseur_id INT NOT NULL REFERENCES fournisseurs(id),
    bon_livraison_id INT REFERENCES bons_livraison(id) ON DELETE SET NULL,
    montant NUMERIC(12,2) NOT NULL,
    date_paiement TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mode_paiement VARCHAR(20) NOT NULL DEFAULT 'ESPECES',
    CONSTRAINT ck_paiements_fournisseurs_montant CHECK (montant > 0),
    CONSTRAINT ck_paiements_fournisseurs_mode CHECK (mode_paiement IN ('ESPECES', 'WAVE', 'OM', 'CARTE', 'AUTRE'))
);

CREATE INDEX idx_commandes_date_vente ON commandes(date_vente);
CREATE INDEX idx_commandes_client ON commandes(client_id);
CREATE INDEX idx_lignes_commandes_commande ON lignes_commandes(commande_id);
CREATE INDEX idx_dettes_client ON dettes(client_id);
CREATE INDEX idx_paiements_dettes_dette ON paiements_dettes(dette_id);
CREATE INDEX idx_bl_fournisseur ON bons_livraison(fournisseur_id);
CREATE INDEX idx_lignes_bl_bon ON lignes_bons_livraison(bon_livraison_id);
CREATE INDEX idx_paiements_fournisseurs_fournisseur ON paiements_fournisseurs(fournisseur_id);

INSERT INTO roles (nom) VALUES
    ('ADMIN'),
    ('VENTE'),
    ('STOCK'),
    ('INVENTAIRE');

INSERT INTO utilisateurs (nom, email, mot_de_passe, role_id) VALUES
    ('Admin Boutique', 'admin@storemanager.sn', 'demo1234', (SELECT id FROM roles WHERE nom = 'ADMIN')),
    ('Chargé de Vente', 'vente@storemanager.sn', 'demo1234', (SELECT id FROM roles WHERE nom = 'VENTE')),
    ('Chargé de Stock', 'stock@storemanager.sn', 'demo1234', (SELECT id FROM roles WHERE nom = 'STOCK')),
    ('Inventaire', 'inventaire@storemanager.sn', 'demo1234', (SELECT id FROM roles WHERE nom = 'INVENTAIRE'));

INSERT INTO produits (nom, prix_unitaire, quantite_stock, seuil_alerte) VALUES
    ('Bidon d''huile 5L', 6000, 5, 5),
    ('Carton de lait', 18000, 12, 5),
    ('Carton de savon', 8500, 3, 5),
    ('Huile de palme 1L', 1500, 0, 5),
    ('Paquet de sucre 1kg', 750, 20, 5),
    ('Sac de riz 50kg', 30000, 8, 3);

INSERT INTO clients (nom, telephone, email, limite_credit) VALUES
    ('Maimouna Diallo', '701122334', NULL, 150000),
    ('Moussa Sarr', '769876543', NULL, 200000),
    ('Fama Diouf', '781234567', NULL, 100000),
    ('Abdou Ndiaye', '770000000', NULL, 100000);

INSERT INTO fournisseurs (nom, telephone, adresse, email) VALUES
    ('Comptoir Céréalier Sénégalais', NULL, NULL, NULL),
    ('Grossiste Diop & Frères', NULL, NULL, NULL),
    ('Sénégal Import-Export', NULL, NULL, NULL);


