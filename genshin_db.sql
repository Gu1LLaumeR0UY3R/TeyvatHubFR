-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 18 mars 2026 à 21:40
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `genshin_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(10) UNSIGNED NOT NULL,
  `pseudo_admin` varchar(50) NOT NULL,
  `email_admin` varchar(100) NOT NULL,
  `mot_de_passe_admin` varchar(255) NOT NULL,
  `role` enum('super_admin','moderateur') NOT NULL DEFAULT 'moderateur'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `amitie`
--

CREATE TABLE `amitie` (
  `id_amitie` int(10) UNSIGNED NOT NULL,
  `fid_demandeur` int(10) UNSIGNED NOT NULL,
  `fid_receveur` int(10) UNSIGNED NOT NULL,
  `statut` enum('en_attente','accepte','refuse') NOT NULL DEFAULT 'en_attente',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `animal_ingredient`
--

CREATE TABLE `animal_ingredient` (
  `fid_animal` int(10) UNSIGNED NOT NULL,
  `fid_ingredient` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `animal_region`
--

CREATE TABLE `animal_region` (
  `fid_animal` int(10) UNSIGNED NOT NULL,
  `fid_region` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `animaux`
--

CREATE TABLE `animaux` (
  `id_animal` int(10) UNSIGNED NOT NULL,
  `nom_animal` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descri_animal` text DEFAULT NULL,
  `fid_TAnimal` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `aptitude`
--

CREATE TABLE `aptitude` (
  `id_aptitude` int(10) UNSIGNED NOT NULL,
  `titre_apti` varchar(100) NOT NULL,
  `descri_apti` text NOT NULL,
  `lvl_apt` tinyint(3) UNSIGNED DEFAULT 1,
  `sub_Apt` text DEFAULT NULL,
  `fid_TypeApti` int(10) UNSIGNED NOT NULL,
  `fid_perso` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `armes`
--

CREATE TABLE `armes` (
  `id_arme` int(10) UNSIGNED NOT NULL,
  `nom_arme` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descr_arme` text DEFAULT NULL,
  `nom_competence` varchar(100) DEFAULT NULL,
  `main_stat_type` varchar(50) DEFAULT NULL,
  `sub_stat_type` varchar(50) DEFAULT NULL,
  `fid_TArmes` int(10) UNSIGNED NOT NULL,
  `fid_etoile` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `arm_stats_niveau`
--

CREATE TABLE `arm_stats_niveau` (
  `id_ASN` int(10) UNSIGNED NOT NULL,
  `lvl_ASN` tinyint(3) UNSIGNED NOT NULL CHECK (`lvl_ASN` between 1 and 90),
  `main_stat` float NOT NULL,
  `subs_stats` float NOT NULL,
  `fid_arme` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `arm_stats_rang`
--

CREATE TABLE `arm_stats_rang` (
  `id_ASR` int(10) UNSIGNED NOT NULL,
  `rang_ASR` tinyint(3) UNSIGNED NOT NULL CHECK (`rang_ASR` between 1 and 5),
  `descri_ASR` text NOT NULL,
  `fid_arme` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `bio`
--

CREATE TABLE `bio` (
  `id_bio` int(10) UNSIGNED NOT NULL,
  `titre_bio` varchar(100) NOT NULL,
  `descri_bio` text NOT NULL,
  `fid_perso` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `chronologie`
--

CREATE TABLE `chronologie` (
  `id_chrono` int(10) UNSIGNED NOT NULL,
  `titre` varchar(150) NOT NULL,
  `resume` text NOT NULL,
  `periode` varchar(100) DEFAULT NULL,
  `ordre` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `fid_region` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `constellation`
--

CREATE TABLE `constellation` (
  `id_const` int(10) UNSIGNED NOT NULL,
  `titre_const` varchar(100) NOT NULL,
  `descri_const` text NOT NULL,
  `fid_perso` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `elements`
--

CREATE TABLE `elements` (
  `id_element` int(10) UNSIGNED NOT NULL,
  `libelle_element` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ennemi`
--

CREATE TABLE `ennemi` (
  `id_ennemi` int(10) UNSIGNED NOT NULL,
  `nom_ennemi` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descri_enn` text DEFAULT NULL,
  `fid_typeEnne` int(10) UNSIGNED NOT NULL,
  `fid_element` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ennemi_region`
--

CREATE TABLE `ennemi_region` (
  `fid_ennemi` int(10) UNSIGNED NOT NULL,
  `fid_region` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etoile`
--

CREATE TABLE `etoile` (
  `id_etoile` int(10) UNSIGNED NOT NULL,
  `libelle` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evenement`
--

CREATE TABLE `evenement` (
  `id_evenement` int(10) UNSIGNED NOT NULL,
  `titre` varchar(100) NOT NULL,
  `descri_courte` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ingrédient`
--

CREATE TABLE `ingrédient` (
  `id_ingredient` int(10) UNSIGNED NOT NULL,
  `nom_ingre` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `joueur`
--

CREATE TABLE `joueur` (
  `id_joueur` int(10) UNSIGNED NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `banniere` varchar(255) DEFAULT NULL,
  `bio_joueur` text DEFAULT NULL,
  `uid_genshin` varchar(20) DEFAULT NULL,
  `date_inscription` datetime NOT NULL DEFAULT current_timestamp(),
  `derniere_connexion` datetime DEFAULT NULL,
  `banni_le` datetime DEFAULT NULL,
  `motif_ban` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `joueur_arme`
--

CREATE TABLE `joueur_arme` (
  `fid_joueur` int(10) UNSIGNED NOT NULL,
  `fid_arme` int(10) UNSIGNED NOT NULL,
  `niveau` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 CHECK (`niveau` between 1 and 90),
  `rang` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 CHECK (`rang` between 1 and 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `joueur_constellation`
--

CREATE TABLE `joueur_constellation` (
  `fid_joueur` int(10) UNSIGNED NOT NULL,
  `fid_perso` int(10) UNSIGNED NOT NULL,
  `fid_constellation` int(10) UNSIGNED NOT NULL,
  `debloquee` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `joueur_personnage`
--

CREATE TABLE `joueur_personnage` (
  `fid_joueur` int(10) UNSIGNED NOT NULL,
  `fid_perso` int(10) UNSIGNED NOT NULL,
  `niveau` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 CHECK (`niveau` between 1 and 90),
  `affinite` tinyint(3) UNSIGNED DEFAULT 0 CHECK (`affinite` between 0 and 10),
  `perso_amelioration` tinyint(1) NOT NULL DEFAULT 0,
  `fid_joueur_arme_joueur` int(10) UNSIGNED DEFAULT NULL,
  `fid_joueur_arme_arme` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `materiaux`
--

CREATE TABLE `materiaux` (
  `id_materiaux` int(10) UNSIGNED NOT NULL,
  `nom_mat` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descri_mat` text DEFAULT NULL,
  `fid_typeM` int(10) UNSIGNED NOT NULL,
  `fid_rareté` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `mate_ennemi`
--

CREATE TABLE `mate_ennemi` (
  `fid_materiaux` int(10) UNSIGNED NOT NULL,
  `fid_ennemi` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personnage`
--

CREATE TABLE `personnage` (
  `id_perso` int(10) UNSIGNED NOT NULL,
  `nom_perso` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `affinite_perso` varchar(50) DEFAULT NULL,
  `fid_TP` int(10) UNSIGNED NOT NULL,
  `fid_etoile` int(10) UNSIGNED NOT NULL,
  `fid_element` int(10) UNSIGNED NOT NULL,
  `fid_TArmes` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personnage_role`
--

CREATE TABLE `personnage_role` (
  `fid_perso` int(10) UNSIGNED NOT NULL,
  `fid_role` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `photo`
--

CREATE TABLE `photo` (
  `id_photo` int(10) UNSIGNED NOT NULL,
  `chemin_photo` varchar(255) NOT NULL,
  `photoable_type` varchar(100) NOT NULL,
  `photoable_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `source_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `plat`
--

CREATE TABLE `plat` (
  `id_plat` int(10) UNSIGNED NOT NULL,
  `nom_plat` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descri_plat` text DEFAULT NULL,
  `fid_rareté` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `plat_ingredient`
--

CREATE TABLE `plat_ingredient` (
  `fid_plat` int(10) UNSIGNED NOT NULL,
  `fid_ingredient` int(10) UNSIGNED NOT NULL,
  `quantite` tinyint(3) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id_produit` int(10) UNSIGNED NOT NULL,
  `libelle_produit` varchar(100) NOT NULL,
  `descri_produit` text DEFAULT NULL,
  `fid_region` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rareté`
--

CREATE TABLE `rareté` (
  `id_rareté` int(10) UNSIGNED NOT NULL,
  `libelle_rareté` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

CREATE TABLE `role` (
  `id_role` int(10) UNSIGNED NOT NULL,
  `libelle_role` varchar(50) NOT NULL,
  `descri_role` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `région`
--

CREATE TABLE `région` (
  `id_region` int(10) UNSIGNED NOT NULL,
  `nom_region` varchar(50) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descri_region` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sous_region`
--

CREATE TABLE `sous_region` (
  `id_sous_region` int(10) UNSIGNED NOT NULL,
  `nom_sous_region` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `fid_region` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `spécialité`
--

CREATE TABLE `spécialité` (
  `id_specialite` int(10) UNSIGNED NOT NULL,
  `libelle_spe` varchar(100) NOT NULL,
  `descri_spe` text DEFAULT NULL,
  `fid_plat` int(10) UNSIGNED NOT NULL,
  `fid_perso` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_animal`
--

CREATE TABLE `type_animal` (
  `id_TAnimal` int(10) UNSIGNED NOT NULL,
  `libelle_TAnimal` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_apti`
--

CREATE TABLE `type_apti` (
  `id_TypeApti` int(10) UNSIGNED NOT NULL,
  `libelle_Apti` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_armes`
--

CREATE TABLE `type_armes` (
  `id_TArmes` int(10) UNSIGNED NOT NULL,
  `libelle_TArme` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_ennemi`
--

CREATE TABLE `type_ennemi` (
  `id_typeEnnemi` int(10) UNSIGNED NOT NULL,
  `libelle_Type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_materiaux`
--

CREATE TABLE `type_materiaux` (
  `id_typeM` int(10) UNSIGNED NOT NULL,
  `libelle_TypeM` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_perso`
--

CREATE TABLE `type_perso` (
  `id_TP` int(10) UNSIGNED NOT NULL,
  `libelle_TP` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `pseudo_admin` (`pseudo_admin`),
  ADD UNIQUE KEY `email_admin` (`email_admin`);

--
-- Index pour la table `amitie`
--
ALTER TABLE `amitie`
  ADD PRIMARY KEY (`id_amitie`),
  ADD UNIQUE KEY `uq_amitie` (`fid_demandeur`,`fid_receveur`),
  ADD KEY `fk_ami_receveur` (`fid_receveur`);

--
-- Index pour la table `animal_ingredient`
--
ALTER TABLE `animal_ingredient`
  ADD PRIMARY KEY (`fid_animal`,`fid_ingredient`),
  ADD KEY `fk_ai_ingre` (`fid_ingredient`);

--
-- Index pour la table `animal_region`
--
ALTER TABLE `animal_region`
  ADD PRIMARY KEY (`fid_animal`,`fid_region`),
  ADD KEY `fk_ar_region` (`fid_region`);

--
-- Index pour la table `animaux`
--
ALTER TABLE `animaux`
  ADD PRIMARY KEY (`id_animal`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_animal_type` (`fid_TAnimal`);

--
-- Index pour la table `aptitude`
--
ALTER TABLE `aptitude`
  ADD PRIMARY KEY (`id_aptitude`),
  ADD KEY `fk_apti_type` (`fid_TypeApti`),
  ADD KEY `fk_apti_perso` (`fid_perso`);

--
-- Index pour la table `armes`
--
ALTER TABLE `armes`
  ADD PRIMARY KEY (`id_arme`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_arme_type` (`fid_TArmes`),
  ADD KEY `fk_arme_etoile` (`fid_etoile`);

--
-- Index pour la table `arm_stats_niveau`
--
ALTER TABLE `arm_stats_niveau`
  ADD PRIMARY KEY (`id_ASN`),
  ADD UNIQUE KEY `uq_arme_niveau` (`fid_arme`,`lvl_ASN`);

--
-- Index pour la table `arm_stats_rang`
--
ALTER TABLE `arm_stats_rang`
  ADD PRIMARY KEY (`id_ASR`),
  ADD UNIQUE KEY `uq_arme_rang` (`fid_arme`,`rang_ASR`);

--
-- Index pour la table `bio`
--
ALTER TABLE `bio`
  ADD PRIMARY KEY (`id_bio`),
  ADD UNIQUE KEY `fid_perso` (`fid_perso`);

--
-- Index pour la table `chronologie`
--
ALTER TABLE `chronologie`
  ADD PRIMARY KEY (`id_chrono`),
  ADD KEY `fk_chrono_region` (`fid_region`);

--
-- Index pour la table `constellation`
--
ALTER TABLE `constellation`
  ADD PRIMARY KEY (`id_const`),
  ADD KEY `fk_const_perso` (`fid_perso`);

--
-- Index pour la table `elements`
--
ALTER TABLE `elements`
  ADD PRIMARY KEY (`id_element`);

--
-- Index pour la table `ennemi`
--
ALTER TABLE `ennemi`
  ADD PRIMARY KEY (`id_ennemi`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_ennemi_type` (`fid_typeEnne`),
  ADD KEY `fk_ennemi_element` (`fid_element`);

--
-- Index pour la table `ennemi_region`
--
ALTER TABLE `ennemi_region`
  ADD PRIMARY KEY (`fid_ennemi`,`fid_region`),
  ADD KEY `fk_er_region` (`fid_region`);

--
-- Index pour la table `etoile`
--
ALTER TABLE `etoile`
  ADD PRIMARY KEY (`id_etoile`);

--
-- Index pour la table `evenement`
--
ALTER TABLE `evenement`
  ADD PRIMARY KEY (`id_evenement`);

--
-- Index pour la table `ingrédient`
--
ALTER TABLE `ingrédient`
  ADD PRIMARY KEY (`id_ingredient`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `joueur`
--
ALTER TABLE `joueur`
  ADD PRIMARY KEY (`id_joueur`),
  ADD UNIQUE KEY `pseudo` (`pseudo`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uid_genshin` (`uid_genshin`);

--
-- Index pour la table `joueur_arme`
--
ALTER TABLE `joueur_arme`
  ADD PRIMARY KEY (`fid_joueur`,`fid_arme`),
  ADD KEY `fk_ja_arme` (`fid_arme`);

--
-- Index pour la table `joueur_constellation`
--
ALTER TABLE `joueur_constellation`
  ADD PRIMARY KEY (`fid_joueur`,`fid_perso`,`fid_constellation`),
  ADD KEY `fk_jc_perso` (`fid_perso`),
  ADD KEY `fk_jc_const` (`fid_constellation`);

--
-- Index pour la table `joueur_personnage`
--
ALTER TABLE `joueur_personnage`
  ADD PRIMARY KEY (`fid_joueur`,`fid_perso`),
  ADD KEY `fk_jp_perso` (`fid_perso`),
  ADD KEY `fk_jp_arme` (`fid_joueur_arme_joueur`,`fid_joueur_arme_arme`);

--
-- Index pour la table `materiaux`
--
ALTER TABLE `materiaux`
  ADD PRIMARY KEY (`id_materiaux`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_mat_type` (`fid_typeM`),
  ADD KEY `fk_mat_rareté` (`fid_rareté`);

--
-- Index pour la table `mate_ennemi`
--
ALTER TABLE `mate_ennemi`
  ADD PRIMARY KEY (`fid_materiaux`,`fid_ennemi`),
  ADD KEY `fk_me_ennemi` (`fid_ennemi`);

--
-- Index pour la table `personnage`
--
ALTER TABLE `personnage`
  ADD PRIMARY KEY (`id_perso`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_perso_type` (`fid_TP`),
  ADD KEY `fk_perso_etoile` (`fid_etoile`),
  ADD KEY `fk_perso_element` (`fid_element`),
  ADD KEY `fk_perso_tarmes` (`fid_TArmes`);

--
-- Index pour la table `personnage_role`
--
ALTER TABLE `personnage_role`
  ADD PRIMARY KEY (`fid_perso`,`fid_role`),
  ADD KEY `fk_pr_role` (`fid_role`);

--
-- Index pour la table `photo`
--
ALTER TABLE `photo`
  ADD PRIMARY KEY (`id_photo`),
  ADD KEY `idx_photoable` (`photoable_type`,`photoable_id`);

--
-- Index pour la table `plat`
--
ALTER TABLE `plat`
  ADD PRIMARY KEY (`id_plat`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_plat_rareté` (`fid_rareté`);

--
-- Index pour la table `plat_ingredient`
--
ALTER TABLE `plat_ingredient`
  ADD PRIMARY KEY (`fid_plat`,`fid_ingredient`),
  ADD KEY `fk_pi_ingre` (`fid_ingredient`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id_produit`),
  ADD KEY `fk_produit_region` (`fid_region`);

--
-- Index pour la table `rareté`
--
ALTER TABLE `rareté`
  ADD PRIMARY KEY (`id_rareté`);

--
-- Index pour la table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id_role`);

--
-- Index pour la table `région`
--
ALTER TABLE `région`
  ADD PRIMARY KEY (`id_region`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `sous_region`
--
ALTER TABLE `sous_region`
  ADD PRIMARY KEY (`id_sous_region`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_sr_region` (`fid_region`);

--
-- Index pour la table `spécialité`
--
ALTER TABLE `spécialité`
  ADD PRIMARY KEY (`id_specialite`),
  ADD UNIQUE KEY `fid_plat` (`fid_plat`),
  ADD KEY `fk_spe_perso` (`fid_perso`);

--
-- Index pour la table `type_animal`
--
ALTER TABLE `type_animal`
  ADD PRIMARY KEY (`id_TAnimal`);

--
-- Index pour la table `type_apti`
--
ALTER TABLE `type_apti`
  ADD PRIMARY KEY (`id_TypeApti`);

--
-- Index pour la table `type_armes`
--
ALTER TABLE `type_armes`
  ADD PRIMARY KEY (`id_TArmes`);

--
-- Index pour la table `type_ennemi`
--
ALTER TABLE `type_ennemi`
  ADD PRIMARY KEY (`id_typeEnnemi`);

--
-- Index pour la table `type_materiaux`
--
ALTER TABLE `type_materiaux`
  ADD PRIMARY KEY (`id_typeM`);

--
-- Index pour la table `type_perso`
--
ALTER TABLE `type_perso`
  ADD PRIMARY KEY (`id_TP`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `amitie`
--
ALTER TABLE `amitie`
  MODIFY `id_amitie` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `animaux`
--
ALTER TABLE `animaux`
  MODIFY `id_animal` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `aptitude`
--
ALTER TABLE `aptitude`
  MODIFY `id_aptitude` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `armes`
--
ALTER TABLE `armes`
  MODIFY `id_arme` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `arm_stats_niveau`
--
ALTER TABLE `arm_stats_niveau`
  MODIFY `id_ASN` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `arm_stats_rang`
--
ALTER TABLE `arm_stats_rang`
  MODIFY `id_ASR` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bio`
--
ALTER TABLE `bio`
  MODIFY `id_bio` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `chronologie`
--
ALTER TABLE `chronologie`
  MODIFY `id_chrono` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `constellation`
--
ALTER TABLE `constellation`
  MODIFY `id_const` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `elements`
--
ALTER TABLE `elements`
  MODIFY `id_element` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ennemi`
--
ALTER TABLE `ennemi`
  MODIFY `id_ennemi` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `etoile`
--
ALTER TABLE `etoile`
  MODIFY `id_etoile` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evenement`
--
ALTER TABLE `evenement`
  MODIFY `id_evenement` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ingrédient`
--
ALTER TABLE `ingrédient`
  MODIFY `id_ingredient` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `joueur`
--
ALTER TABLE `joueur`
  MODIFY `id_joueur` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `materiaux`
--
ALTER TABLE `materiaux`
  MODIFY `id_materiaux` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `personnage`
--
ALTER TABLE `personnage`
  MODIFY `id_perso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `photo`
--
ALTER TABLE `photo`
  MODIFY `id_photo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `plat`
--
ALTER TABLE `plat`
  MODIFY `id_plat` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id_produit` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `rareté`
--
ALTER TABLE `rareté`
  MODIFY `id_rareté` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `role`
--
ALTER TABLE `role`
  MODIFY `id_role` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `région`
--
ALTER TABLE `région`
  MODIFY `id_region` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sous_region`
--
ALTER TABLE `sous_region`
  MODIFY `id_sous_region` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `spécialité`
--
ALTER TABLE `spécialité`
  MODIFY `id_specialite` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_animal`
--
ALTER TABLE `type_animal`
  MODIFY `id_TAnimal` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_apti`
--
ALTER TABLE `type_apti`
  MODIFY `id_TypeApti` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_armes`
--
ALTER TABLE `type_armes`
  MODIFY `id_TArmes` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_ennemi`
--
ALTER TABLE `type_ennemi`
  MODIFY `id_typeEnnemi` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_materiaux`
--
ALTER TABLE `type_materiaux`
  MODIFY `id_typeM` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_perso`
--
ALTER TABLE `type_perso`
  MODIFY `id_TP` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `amitie`
--
ALTER TABLE `amitie`
  ADD CONSTRAINT `fk_ami_demandeur` FOREIGN KEY (`fid_demandeur`) REFERENCES `joueur` (`id_joueur`),
  ADD CONSTRAINT `fk_ami_receveur` FOREIGN KEY (`fid_receveur`) REFERENCES `joueur` (`id_joueur`);

--
-- Contraintes pour la table `animal_ingredient`
--
ALTER TABLE `animal_ingredient`
  ADD CONSTRAINT `fk_ai_animal` FOREIGN KEY (`fid_animal`) REFERENCES `animaux` (`id_animal`),
  ADD CONSTRAINT `fk_ai_ingre` FOREIGN KEY (`fid_ingredient`) REFERENCES `ingrédient` (`id_ingredient`);

--
-- Contraintes pour la table `animal_region`
--
ALTER TABLE `animal_region`
  ADD CONSTRAINT `fk_ar_animal` FOREIGN KEY (`fid_animal`) REFERENCES `animaux` (`id_animal`),
  ADD CONSTRAINT `fk_ar_region` FOREIGN KEY (`fid_region`) REFERENCES `région` (`id_region`);

--
-- Contraintes pour la table `animaux`
--
ALTER TABLE `animaux`
  ADD CONSTRAINT `fk_animal_type` FOREIGN KEY (`fid_TAnimal`) REFERENCES `type_animal` (`id_TAnimal`);

--
-- Contraintes pour la table `aptitude`
--
ALTER TABLE `aptitude`
  ADD CONSTRAINT `fk_apti_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`),
  ADD CONSTRAINT `fk_apti_type` FOREIGN KEY (`fid_TypeApti`) REFERENCES `type_apti` (`id_TypeApti`);

--
-- Contraintes pour la table `armes`
--
ALTER TABLE `armes`
  ADD CONSTRAINT `fk_arme_etoile` FOREIGN KEY (`fid_etoile`) REFERENCES `etoile` (`id_etoile`),
  ADD CONSTRAINT `fk_arme_type` FOREIGN KEY (`fid_TArmes`) REFERENCES `type_armes` (`id_TArmes`);

--
-- Contraintes pour la table `arm_stats_niveau`
--
ALTER TABLE `arm_stats_niveau`
  ADD CONSTRAINT `fk_asn_arme` FOREIGN KEY (`fid_arme`) REFERENCES `armes` (`id_arme`);

--
-- Contraintes pour la table `arm_stats_rang`
--
ALTER TABLE `arm_stats_rang`
  ADD CONSTRAINT `fk_asr_arme` FOREIGN KEY (`fid_arme`) REFERENCES `armes` (`id_arme`);

--
-- Contraintes pour la table `bio`
--
ALTER TABLE `bio`
  ADD CONSTRAINT `fk_bio_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`);

--
-- Contraintes pour la table `chronologie`
--
ALTER TABLE `chronologie`
  ADD CONSTRAINT `fk_chrono_region` FOREIGN KEY (`fid_region`) REFERENCES `région` (`id_region`);

--
-- Contraintes pour la table `constellation`
--
ALTER TABLE `constellation`
  ADD CONSTRAINT `fk_const_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`);

--
-- Contraintes pour la table `ennemi`
--
ALTER TABLE `ennemi`
  ADD CONSTRAINT `fk_ennemi_element` FOREIGN KEY (`fid_element`) REFERENCES `elements` (`id_element`),
  ADD CONSTRAINT `fk_ennemi_type` FOREIGN KEY (`fid_typeEnne`) REFERENCES `type_ennemi` (`id_typeEnnemi`);

--
-- Contraintes pour la table `ennemi_region`
--
ALTER TABLE `ennemi_region`
  ADD CONSTRAINT `fk_er_ennemi` FOREIGN KEY (`fid_ennemi`) REFERENCES `ennemi` (`id_ennemi`),
  ADD CONSTRAINT `fk_er_region` FOREIGN KEY (`fid_region`) REFERENCES `région` (`id_region`);

--
-- Contraintes pour la table `joueur_arme`
--
ALTER TABLE `joueur_arme`
  ADD CONSTRAINT `fk_ja_arme` FOREIGN KEY (`fid_arme`) REFERENCES `armes` (`id_arme`),
  ADD CONSTRAINT `fk_ja_joueur` FOREIGN KEY (`fid_joueur`) REFERENCES `joueur` (`id_joueur`);

--
-- Contraintes pour la table `joueur_constellation`
--
ALTER TABLE `joueur_constellation`
  ADD CONSTRAINT `fk_jc_const` FOREIGN KEY (`fid_constellation`) REFERENCES `constellation` (`id_const`),
  ADD CONSTRAINT `fk_jc_joueur` FOREIGN KEY (`fid_joueur`) REFERENCES `joueur` (`id_joueur`),
  ADD CONSTRAINT `fk_jc_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`);

--
-- Contraintes pour la table `joueur_personnage`
--
ALTER TABLE `joueur_personnage`
  ADD CONSTRAINT `fk_jp_arme` FOREIGN KEY (`fid_joueur_arme_joueur`,`fid_joueur_arme_arme`) REFERENCES `joueur_arme` (`fid_joueur`, `fid_arme`),
  ADD CONSTRAINT `fk_jp_joueur` FOREIGN KEY (`fid_joueur`) REFERENCES `joueur` (`id_joueur`),
  ADD CONSTRAINT `fk_jp_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`);

--
-- Contraintes pour la table `materiaux`
--
ALTER TABLE `materiaux`
  ADD CONSTRAINT `fk_mat_rareté` FOREIGN KEY (`fid_rareté`) REFERENCES `rareté` (`id_rareté`),
  ADD CONSTRAINT `fk_mat_type` FOREIGN KEY (`fid_typeM`) REFERENCES `type_materiaux` (`id_typeM`);

--
-- Contraintes pour la table `mate_ennemi`
--
ALTER TABLE `mate_ennemi`
  ADD CONSTRAINT `fk_me_ennemi` FOREIGN KEY (`fid_ennemi`) REFERENCES `ennemi` (`id_ennemi`),
  ADD CONSTRAINT `fk_me_mat` FOREIGN KEY (`fid_materiaux`) REFERENCES `materiaux` (`id_materiaux`);

--
-- Contraintes pour la table `personnage`
--
ALTER TABLE `personnage`
  ADD CONSTRAINT `fk_perso_element` FOREIGN KEY (`fid_element`) REFERENCES `elements` (`id_element`),
  ADD CONSTRAINT `fk_perso_etoile` FOREIGN KEY (`fid_etoile`) REFERENCES `etoile` (`id_etoile`),
  ADD CONSTRAINT `fk_perso_tarmes` FOREIGN KEY (`fid_TArmes`) REFERENCES `type_armes` (`id_TArmes`),
  ADD CONSTRAINT `fk_perso_type` FOREIGN KEY (`fid_TP`) REFERENCES `type_perso` (`id_TP`);

--
-- Contraintes pour la table `personnage_role`
--
ALTER TABLE `personnage_role`
  ADD CONSTRAINT `fk_pr_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`),
  ADD CONSTRAINT `fk_pr_role` FOREIGN KEY (`fid_role`) REFERENCES `role` (`id_role`);

--
-- Contraintes pour la table `plat`
--
ALTER TABLE `plat`
  ADD CONSTRAINT `fk_plat_rareté` FOREIGN KEY (`fid_rareté`) REFERENCES `rareté` (`id_rareté`);

--
-- Contraintes pour la table `plat_ingredient`
--
ALTER TABLE `plat_ingredient`
  ADD CONSTRAINT `fk_pi_ingre` FOREIGN KEY (`fid_ingredient`) REFERENCES `ingrédient` (`id_ingredient`),
  ADD CONSTRAINT `fk_pi_plat` FOREIGN KEY (`fid_plat`) REFERENCES `plat` (`id_plat`);

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `fk_produit_region` FOREIGN KEY (`fid_region`) REFERENCES `région` (`id_region`);

--
-- Contraintes pour la table `sous_region`
--
ALTER TABLE `sous_region`
  ADD CONSTRAINT `fk_sr_region` FOREIGN KEY (`fid_region`) REFERENCES `région` (`id_region`);

--
-- Contraintes pour la table `spécialité`
--
ALTER TABLE `spécialité`
  ADD CONSTRAINT `fk_spe_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`),
  ADD CONSTRAINT `fk_spe_plat` FOREIGN KEY (`fid_plat`) REFERENCES `plat` (`id_plat`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
