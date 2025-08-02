-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 02 أغسطس 2025 الساعة 05:57
-- إصدار الخادم: 10.4.21-MariaDB
-- PHP Version: 8.0.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `raseen`
--

-- --------------------------------------------------------

--
-- بنية الجدول `agreements`
--

CREATE TABLE `agreements` (
  `id` int(11) NOT NULL,
  `investment_id` int(11) NOT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `investor_signature_path` varchar(255) DEFAULT NULL,
  `entrepreneur_signature_path` varchar(255) DEFAULT NULL,
  `signed_at_investor` datetime DEFAULT NULL,
  `signed_at_entrepreneur` datetime DEFAULT NULL,
  `status` enum('pending_investor','pending_entrepreneur','completed') DEFAULT 'pending_investor',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- بنية الجدول `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `one_line_summary` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_population` int(11) DEFAULT NULL,
  `male_percentage` decimal(5,2) DEFAULT NULL,
  `female_percentage` decimal(5,2) DEFAULT NULL,
  `key_attraction` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `main_support` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `momentum_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_projects` tinyint(1) DEFAULT 0,
  `cta_text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_updated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `cities`
--

INSERT INTO `cities` (`id`, `slug`, `name_ar`, `name_en`, `one_line_summary`, `total_population`, `male_percentage`, `female_percentage`, `key_attraction`, `main_support`, `momentum_note`, `has_projects`, `cta_text`, `last_updated`) VALUES
(1, 'riyadh', 'الرياض', 'Riyadh', 'عاصمة اقتصادية بدعم رؤية 2030.', 4205961, '62.60', '37.40', 'دعم حكومي ومحور اقتصادي', 'حاضنات ورؤية 2030', 'زخم ريادي عالي', 1, 'عرض المشاريع', '2025-07-31'),
(2, 'unaizah', 'عنيزة', 'Unaizah', 'مدينة زراعية وتاريخية في القصيم مع اقتصاد تمر قوي.', 184600, '62.10', '37.90', 'زراعة وتمور', 'مهرجانات التمر وبنية زراعية', 'مركز مركزي في القصيم وقرب من بريدة', 0, 'عرض المشاريع', '2024-05-10'),
(3, 'buraidah', 'بريدة', 'Buraidah', 'عاصمة القصيم الزراعية ومركز تجاري لتمور وحركة داخلية.', 745353, '62.10', '37.90', 'التمر والأسواق الزراعية', 'اقتصاد زراعي مدعوم محلياً', 'نشاط تجاري زراعي ثابت', 0, 'عرض المشاريع', '2022-05-10'),
(4, 'dammam', 'الدمام', 'Dammam', 'محور نفطي وصناعي في المنطقة الشرقية.', 1386166, '60.20', '39.80', 'صناعة وطاقة', 'بنية تحتية نفطية ولوجستية متقدمة', 'مركز محوري لصادرات الطاقة والتوسع الصناعي', 1, 'عرض المشاريع', '2023-01-01'),
(5, 'khobar', 'الخبر', 'Khobar', 'مركز تجاري وخدمي في الشرقية قريب من الدمام.', 165799, '60.20', '39.80', 'خدمات واستثمار تجاري', 'قرب من محاور الطاقة والأسواق', 'دعم من شبكة مدن المنطقة الشرقية', 0, 'عرض المشاريع', '2022-01-01'),
(6, 'al-ahsa', 'الأحساء', 'Al-Ahsa', 'واحة زراعية كبيرة وبوابة تاريخية واقتصادية في الشرقية.', 1104267, '60.20', '39.80', 'واحة وتمور وموارد مائية', 'اقتصاد زراعي قوي وتاريخي', 'توازن بين الزراعة والتطوير الحضري', 0, 'عرض المشاريع', '2022-01-01'),
(7, 'jubail', 'الجبيل', 'Jubail', 'مدينة صناعية كبرى ومحور بتروكيميائي.', 711829, '60.20', '39.80', 'بتروكيميائيات وصناعة ثقيلة', 'مدينة صناعية متطورة وبنية قوية', 'نمو صناعي مستمر وتوسّع مشاريع', 1, 'عرض المشاريع', '2025-01-01'),
(8, 'medina', 'المدينة المنورة', 'Medina', 'مدينة دينية وسياحية ذات زخم مزدوج من الحج والاستقرار.', 1624660, '62.10', '37.90', 'سياحة دينية وزيارات مستمرة', 'زيارات الحج والعمرة والبنية الخدمية', 'تدفق زوار ثابت وفرص خدماتية', 1, 'عرض المشاريع', '2025-01-01'),
(9, 'makkah', 'مكة', 'Mecca', 'أقدس مدينة إسلامية مع اقتصاد ضخم مرتبط بالحج والعمرة.', 2218580, '62.10', '37.90', 'سياحة دينية عالمية', 'حج وعمرة وبنية تحتية ضخمة', 'ارتفاع الطلب الموسمي والخدماتي', 1, 'عرض المشاريع', '2025-01-01'),
(10, 'yanbu', 'ينبع', 'Yanbu', 'ميناء صناعي على البحر الأحمر مع قطاع نفطي وبتروكيماوي.', 371327, '62.10', '37.90', 'موانئ وصناعة نفطية', 'مرافق صناعية واستثمارات بحرية', 'توسع صناعي وتصدير', 0, 'عرض المشاريع', '2025-01-01'),
(11, 'taif', 'الطائف', 'Taif', 'وجهة سياحية جبلية قريبة من مكة وتتمتع بمناخ مميز.', 726144, '62.10', '37.90', 'سياحة ومناخ معتدل', 'قطاع ضيافة وتطوير سياحي', 'تزايد الاهتمام بالموسم الصيفي', 0, 'عرض المشاريع', '2025-01-01'),
(12, 'abha', 'أبها', 'Abha', 'مدينة جبلية سياحية بجنوب المملكة بمناخ مميز.', 334290, '62.10', '37.90', 'سياحة جبلية ومناخ معتدل', 'تطوير سياحي إقليمي', 'زيادة نمو سكاني وحركة داخلية', 0, 'عرض المشاريع', '2022-01-01'),
(13, 'al-namas', 'النماص', 'Al-Namas', 'محافظة جبلية صغيرة في عسير مع طبيعة وغابات.', 38409, '62.10', '37.90', 'طبيعة وجبال', 'سياحة محلية واستدامة بيئية', 'توجه لتطوير السياحة البيئية', 0, 'عرض المشاريع', '2022-01-01'),
(14, 'najran', 'نجران', 'Najran', 'مدينة جنوبية حدودية بنمو سريع وتنوع ثقافي.', 449729, '62.10', '37.90', 'تجارة حدودية وتنوع ثقافي', 'نمو حضري سريع', 'توسع سكاني قوي', 0, 'عرض المشاريع', '2025-01-01'),
(15, 'jazan', 'جازان', 'Jazan', 'منطقة كثيفة سكانية على الحدود الجنوبية مع اقتصاد متنوع.', 1404997, '62.10', '37.90', 'حدود وتنوع اقتصادي', 'تجارة وزراعة مكثفة', 'أعلى كثافة سكانية في المملكة بالإقليم', 0, 'عرض المشاريع', '2022-01-01'),
(16, 'al-baha', 'الباحة', 'Al-Baha', 'إقليم جبلي مستدام من حيث جودة حياة وبيئة.', 339174, '62.10', '37.90', 'استدامة وجودة حياة', 'بيئة جبلية جذابة واستقرار', 'قليل الكثافة نسبياً مع تركيز على الاستدامة', 0, 'عرض المشاريع', '2022-01-01'),
(17, 'hail', 'حائل', 'Hail', 'مدينة وسطى استراتيجية مع نمو حضري معتدل.', 431270, '62.10', '37.90', 'موقع استراتيجي وسوق داخلي', 'توسّع حضري ومشروعات إقليمية', 'نمو سكاني تدريجي', 0, 'عرض المشاريع', '2025-01-01'),
(18, 'jeddah', 'جدة', 'Jeddah', 'محور تجاري غربي وبوابة البحر الأحمر.', 4000000, '62.10', '37.90', 'تجارة وبحرية', 'بوابة غربية وتبادل دولي', 'نشاط تجاري مكثف', 1, 'عرض المشاريع', '2025-07-31');

-- --------------------------------------------------------

--
-- بنية الجدول `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `investor_id` int(11) NOT NULL,
  `entrepreneur_id` int(11) NOT NULL,
  `started_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `conversations`
--

INSERT INTO `conversations` (`id`, `project_id`, `investor_id`, `entrepreneur_id`, `started_at`) VALUES
(4, 33, 3, 15, '2025-07-30 03:10:27'),
(5, 39, 3, 21, '2025-07-30 03:11:37'),
(6, 30, 3, 12, '2025-07-30 03:17:09'),
(7, 37, 3, 19, '2025-07-30 03:19:35'),
(11, 39, 4, 21, '2025-07-31 15:49:25'),
(14, 36, 4, 18, '2025-07-31 19:57:50'),
(15, 28, 4, 10, '2025-08-01 08:50:09'),
(16, 35, 5, 17, '2025-08-01 19:37:09'),
(17, 39, 5, 21, '2025-08-02 05:45:37');

-- --------------------------------------------------------

--
-- بنية الجدول `entrepreneurs`
--

CREATE TABLE `entrepreneurs` (
  `ID` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `national_id` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `entrepreneurs`
--

INSERT INTO `entrepreneurs` (`ID`, `first_name`, `last_name`, `national_id`, `phone`, `email`, `password`) VALUES
(9, 'سالم ', 'راشد', '1045567890', '0581234567', 'salem.rashed@email.com', '$2y$10$t2D0y./DsmVwrq/j0W/tn.wYvYLWbrfLuRBdANhKvtVS/3Oxzu6LO'),
(10, 'سعود', 'ناصر', '1029384756', '0556789123', 'saud.nasser@email.com', '$2y$10$Y3wtqZZFeVhKg4ZuCDk5wOq57JNAQ.LRll4DVUREu3cGXXoW1qjZ.'),
(11, 'ريم', 'عبدالعزيز', '1076543210', '0567894321', 'reem.aziz@email.com', '$2y$10$TgVuCp/vHuRSeOJKHT7h4OBaLEYX7e.x7Tjg9qNxoE5zK/g5AXcky'),
(12, 'خالد', 'فهد', '1011122233', '0571237890', 'khaled.fahad@email.com', '$2y$10$aqJ2xgHZvKu0Olrz2wQXgeKpvaGH72YbGK2I5LAjsDyQ1A.n72GiS'),
(13, 'مها', 'سليمان', '1054321987', '0589012345', 'maha.sulaiman@email.com', '$2y$10$b2x9dBH65L2Ae.AeC8Hqp.ybfFlxgms7VUaidQiX14XcSI8761ihW'),
(14, 'نوف', 'راشد', '1065432198', '0598765432', 'nouf.rashed@email.com', '$2y$10$20VFS1gbW353Yl1aElpHw.tGh7TvsL7s6QTfMDofl5AB9Y5.OmYaG'),
(15, 'فهد', 'منصور', '1045678912', '0587654321', 'fahad.mansour@email.com', '$2y$10$R2KIAFgfKpEBzIcKny/NTu0771p2XVvPmclxia0Gx6MbWXaFe0F8.'),
(16, 'ياسر', 'حمود', '1033214567', '0579987766', 'yasser.h@madrasa.com', '$2y$10$.iBvpE27K9qSH1yMmzDor.R8HDP2bEnTYGGHcqID7aU59NXskWgbO'),
(17, 'جود', 'علي', '1099887766', '0564433221', 'jood.ali@crafts.com', '$2y$10$5.4/NOuYq2ON8fkCsirs8egB07qalp/aBMginxXImBBATgk/7U/Di'),
(18, 'مها', 'سليمان', '1298376423', '0593874526', 'maha.suliman@gmail.com', '$2y$10$.cEdD5IIghHAXgVCysoqP.qEglRt1dgN/IycsX8tqcdJjPpJ.xRDa'),
(19, 'سحر ', 'فيصل', '1298376412', '0581234562', 'sahar.faisal@gmail.com', '$2y$10$o2FlGGAXskH4igo.gQ6ZpeXSDv6yyXNNVBtLBzCMKxAULv1hB62C.'),
(20, 'مشعل', 'عبدالعزيز', '1045567536', '0593874364', 'meshaal.abdulaziz@gmail.com', '$2y$10$U6t.tDgaORDyrvPU/ILA6u.e3fxJTaQMXXvEcWAjPZt2uFGynU5OK'),
(21, 'احمد', 'سامي', '1034567890', '0559876543', 'ahmed.sami@email.com', '$2y$10$GJufRbCD0CzfUDKg/pPJgeVia..989.vPZNcAWg4.abi2u92E9jUe'),
(22, 'عمر ', 'يوسف', '1076504321', '0567719931', 'omar.yousef@gmail.com', '$2y$10$/G2G0.ke0bJWRc82SY3NoumCHZAl5m6Ia4WjGfdCLodH.7wnIxoDq');

-- --------------------------------------------------------

--
-- بنية الجدول `ideas`
--

CREATE TABLE `ideas` (
  `entrepreneur_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `idea_name` varchar(255) NOT NULL,
  `idea_summary` varchar(300) NOT NULL,
  `problem_statement` text NOT NULL,
  `proposed_solution` text NOT NULL,
  `field` varchar(50) NOT NULL,
  `sub_fields` varchar(255) DEFAULT NULL,
  `other_field_detail` varchar(255) DEFAULT NULL,
  `target` varchar(255) NOT NULL,
  `target_other` varchar(255) DEFAULT NULL,
  `has_experience` enum('نعم','لا') NOT NULL,
  `has_partner` enum('نعم','لا') NOT NULL,
  `investment_goal` varchar(50) NOT NULL,
  `sell_price` decimal(15,2) DEFAULT NULL,
  `readiness_level` int(11) NOT NULL,
  `has_support_file` enum('نعم','لا') DEFAULT NULL,
  `support_file` varchar(255) DEFAULT NULL,
  `market_research_summary` text DEFAULT NULL,
  `main_challenge` text DEFAULT NULL,
  `next_step` text DEFAULT NULL,
  `score` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `ideas`
--

INSERT INTO `ideas` (`entrepreneur_id`, `id`, `idea_name`, `idea_summary`, `problem_statement`, `proposed_solution`, `field`, `sub_fields`, `other_field_detail`, `target`, `target_other`, `has_experience`, `has_partner`, `investment_goal`, `sell_price`, `readiness_level`, `has_support_file`, `support_file`, `market_research_summary`, `main_challenge`, `next_step`, `score`) VALUES
(9, 1, 'منصة تسويق منتجات الأسر المنتجة', 'منصة إلكترونية تربط الأسر المنتجة بالعملاء لبيع المنتجات المنزلية بموثوقية وسهولة.', 'صعوبة وصول الأسر المنتجة للعملاء وصعوبة بناء الثقة بالمنتجات المنزلية.', 'منصة بواجهة سهلة تدعم الدفع الإلكتروني وتوصيل المنتجات مع نظام تقييم للمصداقية.', 'business', '', '', 'الأفراد,ذوي الدخل المحدود', '', 'نعم', 'لا', 'استثمار كامل', '0.00', 50, 'لا', '', '', '', '', 42);

-- --------------------------------------------------------

--
-- بنية الجدول `investments`
--

CREATE TABLE `investments` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `investor_id` int(11) NOT NULL,
  `entrepreneur_id` int(11) NOT NULL,
  `type` enum('equity','loan') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `equity_percentage` decimal(5,2) DEFAULT NULL,
  `loan_term_months` int(11) DEFAULT NULL,
  `interest_rate` decimal(5,2) DEFAULT NULL,
  `repayment_start_date` date DEFAULT NULL,
  `trust_score_at_proposal` int(11) NOT NULL,
  `proposal_notes` text DEFAULT NULL,
  `agreement_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','under_review','accepted','rejected','negotiating') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `investor_accepted_terms` tinyint(1) DEFAULT 0,
  `entrepreneur_accepted_terms` tinyint(1) DEFAULT 0,
  `investor_accepted_at` datetime DEFAULT NULL,
  `entrepreneur_accepted_at` datetime DEFAULT NULL,
  `investor_signed` tinyint(1) NOT NULL DEFAULT 0,
  `entrepreneur_signed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `investments`
--

INSERT INTO `investments` (`id`, `project_id`, `investor_id`, `entrepreneur_id`, `type`, `amount`, `equity_percentage`, `loan_term_months`, `interest_rate`, `repayment_start_date`, `trust_score_at_proposal`, `proposal_notes`, `agreement_file`, `status`, `created_at`, `updated_at`, `investor_accepted_terms`, `entrepreneur_accepted_terms`, `investor_accepted_at`, `entrepreneur_accepted_at`, `investor_signed`, `entrepreneur_signed`) VALUES
(24, 28, 4, 10, 'equity', '150000.00', '20.00', NULL, NULL, NULL, 60, 'استثمار مباشر بنفس مبلغ المشروع المطلوب.', NULL, 'accepted', '2025-08-01 10:29:25', '2025-08-01 10:43:26', 0, 0, NULL, NULL, 1, 1),
(25, 35, 5, 17, 'equity', '60000.00', '0.00', NULL, NULL, NULL, 65, 'استثمار مباشر بنفس مبلغ المشروع المطلوب.', NULL, 'accepted', '2025-08-01 19:38:09', '2025-08-01 19:39:04', 0, 0, NULL, NULL, 1, 1),
(26, 39, 5, 21, 'equity', '450000.00', '18.00', NULL, NULL, NULL, 95, 'استثمار مباشر بنفس مبلغ المشروع المطلوب.', NULL, 'accepted', '2025-08-02 05:45:37', '2025-08-02 05:45:37', 0, 0, NULL, NULL, 0, 0);

-- --------------------------------------------------------

--
-- بنية الجدول `investors`
--

CREATE TABLE `investors` (
  `ID` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `national_id` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `interests` text NOT NULL,
  `region` text NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `investors`
--

INSERT INTO `investors` (`ID`, `first_name`, `last_name`, `national_id`, `phone`, `email`, `interests`, `region`, `password`) VALUES
(3, 'سالم ', 'راشد', '1045567890', '0581234567', 'salem.rashed@example.com', 'لوجستية,تجارية,سياحية', 'الشرقية,الوسطى,الجنوبية', '$2y$10$ELIr2/M6NI./IG4CoHVVo.pZB9jfQARKnMsMQmE9fqKk6jSXJ3.Ue'),
(4, 'لين', 'عبدالله', '10765043213', '0567719931', 'omar.yousf@gmail.com', '', 'الوسطى,الغربية', '$2y$10$i3PcbM0V/E3QrC7tytMExuGBgruckGF5CQGmTJgn1cF1X0nld6w6W'),
(5, 'سالم ', 'راشد', '1076504321', '0567719959', 'bin.salamah187@gmail.con', '', 'الشرقية,الغربية,الجنوبية', '$2y$10$UkPs2sxLVVLjdakE1AZO4.Ljn7RoYQ8IYTtKaAJQIcRhZge6FE8xW');

-- --------------------------------------------------------

--
-- بنية الجدول `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_type` enum('investor','entrepreneur') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `sent_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_type`, `sender_id`, `message_text`, `sent_at`) VALUES
(32, 14, 'investor', 4, 'Equity proposal created with status accepted. Notes: استثمار مباشر بنفس مبلغ المشروع المطلوب.', '2025-07-31 19:57:58'),
(35, 15, 'investor', 4, 'Equity proposal created with status accepted. Notes: استثمار مباشر بنفس مبلغ المشروع المطلوب.', '2025-08-01 08:50:09'),
(36, 15, 'investor', 4, 'Equity proposal created with status accepted. Notes: استثمار مباشر بنفس مبلغ المشروع المطلوب.', '2025-08-01 08:55:10'),
(37, 15, 'investor', 4, 'Equity proposal created with status accepted. Notes: استثمار مباشر بنفس مبلغ المشروع المطلوب.', '2025-08-01 09:04:19'),
(38, 15, 'investor', 4, 'Equity proposal created with status accepted. Notes: استثمار مباشر بنفس مبلغ المشروع المطلوب.', '2025-08-01 09:35:18'),
(39, 15, 'investor', 4, 'Equity proposal created with status accepted. Notes: استثمار مباشر بنفس مبلغ المشروع المطلوب.', '2025-08-01 10:09:14'),
(40, 15, 'investor', 4, 'Equity proposal created with status accepted. Notes: استثمار مباشر بنفس مبلغ المشروع المطلوب.', '2025-08-01 10:09:40'),
(41, 15, 'investor', 4, 'Equity proposal created with status accepted. Notes: استثمار مباشر بنفس مبلغ المشروع المطلوب.', '2025-08-01 10:16:50'),
(42, 15, 'investor', 4, 'Equity proposal created with status accepted. Notes: استثمار مباشر بنفس مبلغ المشروع المطلوب.', '2025-08-01 10:18:41'),
(43, 15, 'investor', 4, 'Equity proposal created with status accepted. Notes: استثمار مباشر بنفس مبلغ المشروع المطلوب.', '2025-08-01 10:29:25'),
(44, 16, 'investor', 5, 'هاي', '2025-08-01 19:37:12'),
(45, 16, 'entrepreneur', 17, 'هاي', '2025-08-01 19:37:25'),
(46, 16, 'investor', 5, 'Equity proposal created with status accepted. Notes: استثمار مباشر بنفس مبلغ المشروع المطلوب.', '2025-08-01 19:38:09'),
(47, 17, 'investor', 5, 'Equity proposal created with status accepted. Notes: استثمار مباشر بنفس مبلغ المشروع المطلوب.', '2025-08-02 05:45:37');

-- --------------------------------------------------------

--
-- بنية الجدول `platform_ratings`
--

CREATE TABLE `platform_ratings` (
  `id` int(11) NOT NULL,
  `investor_id` int(11) NOT NULL,
  `investment_id` int(11) NOT NULL,
  `overall_star` tinyint(4) NOT NULL CHECK (`overall_star` between 1 and 5),
  `usability_star` tinyint(4) NOT NULL CHECK (`usability_star` between 1 and 5),
  `trust_star` tinyint(4) NOT NULL CHECK (`trust_star` between 1 and 5),
  `communication_star` tinyint(4) NOT NULL CHECK (`communication_star` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `platform_ratings`
--

INSERT INTO `platform_ratings` (`id`, `investor_id`, `investment_id`, `overall_star`, `usability_star`, `trust_star`, `communication_star`, `comment`, `created_at`, `updated_at`) VALUES
(2, 4, 24, 5, 5, 5, 5, 'اا', '2025-08-01 15:44:00', '2025-08-01 15:44:00'),
(3, 5, 25, 5, 5, 5, 5, 'لل', '2025-08-01 19:39:26', '2025-08-01 19:39:26');

-- --------------------------------------------------------

--
-- بنية الجدول `projects`
--

CREATE TABLE `projects` (
  `entrepreneur_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `project_summary` text NOT NULL,
  `commercial_registration` varchar(100) NOT NULL,
  `field` varchar(50) NOT NULL,
  `sub_fields` text DEFAULT NULL,
  `other_field_detail` varchar(255) DEFAULT NULL,
  `region` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `has_team` tinyint(1) DEFAULT NULL,
  `team_size` int(11) DEFAULT NULL,
  `has_rent` tinyint(1) DEFAULT NULL,
  `rent_cost` decimal(10,2) DEFAULT NULL,
  `has_salaries` tinyint(1) DEFAULT NULL,
  `salary_range` decimal(10,2) DEFAULT NULL,
  `has_operating_costs` tinyint(1) DEFAULT NULL,
  `operating_costs` decimal(10,2) DEFAULT NULL,
  `has_marketing` tinyint(1) DEFAULT NULL,
  `marketing_cost` decimal(10,2) DEFAULT NULL,
  `has_other_costs` tinyint(1) DEFAULT NULL,
  `other_costs` decimal(10,2) DEFAULT NULL,
  `finance_type` varchar(20) DEFAULT NULL,
  `requested_amount_equity` decimal(12,2) DEFAULT NULL,
  `investment_reason_equity` text DEFAULT NULL,
  `investor_share` decimal(5,2) DEFAULT NULL,
  `expected_monthly_return_equity` decimal(12,2) DEFAULT NULL,
  `loan_amount` decimal(12,2) DEFAULT NULL,
  `loan_reason` text DEFAULT NULL,
  `loan_repayment_type` varchar(50) DEFAULT NULL,
  `installment_period` int(11) DEFAULT NULL,
  `requested_amount` decimal(12,2) NOT NULL,
  `investment_reason` text NOT NULL,
  `expected_monthly_return_before` decimal(12,2) DEFAULT NULL,
  `expected_monthly_return` decimal(12,2) DEFAULT NULL,
  `trust_score` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `projects`
--

INSERT INTO `projects` (`entrepreneur_id`, `id`, `project_name`, `project_summary`, `commercial_registration`, `field`, `sub_fields`, `other_field_detail`, `region`, `city`, `has_team`, `team_size`, `has_rent`, `rent_cost`, `has_salaries`, `salary_range`, `has_operating_costs`, `operating_costs`, `has_marketing`, `marketing_cost`, `has_other_costs`, `other_costs`, `finance_type`, `requested_amount_equity`, `investment_reason_equity`, `investor_share`, `expected_monthly_return_equity`, `loan_amount`, `loan_reason`, `loan_repayment_type`, `installment_period`, `requested_amount`, `investment_reason`, `expected_monthly_return_before`, `expected_monthly_return`, `trust_score`) VALUES
(9, 27, 'غلاف بلس – تغليف ذكي وصديق للبيئة', '\"غلاف بلس\" هو مصنع تغليف حديث يقدّم حلول تغليف مبتكرة وصديقة للبيئة لقطاع الأغذية والمنتجات الاستهلاكية. يهدف المصنع إلى تزويد الشركات الصغيرة والمتوسطة بمواد تغليف ذات جودة عالية، تقلل التكلفة التشغيلية وتحافظ على البيئة، مع خدمات تصميم وتخصيص متقدمة.', '2053344990', 'industrial', 'مصانع صغيرة', '', 'الوسطى', 'عنيزة', 1, 9, 1, '25000.00', 1, '9000.00', 1, '8000.00', 1, '3000.00', 1, '5000.00', 'loan', '0.00', '0', '0.00', '0.00', '120000.00', 'توسيع خطوط الإنتاج بشراء آلات أوتوماتيكية حديثة، ودعم البحث والتطوير لإنتاج مواد تغليف مبتكرة ذات تكلفة أقل وكفاءة أعلى.\r\n', '0', 24, '120000.00', '0', '30000.00', '90000.00', 92),
(10, 28, 'مساحة كريتيف', 'مساحة عمل مشتركة للمستقلين ورواد الأعمال توفر بيئة محفزة، غرف اجتماعات، إنترنت سريع، وورش تطوير مهني. تستهدف الفئات الشابة وأصحاب المشاريع الناشئة في بيئة متنامية.', '1012345678', 'business', 'محلات', '', 'الغربية', 'جدة', 1, 0, 0, '0.00', 1, '0.00', 1, '0.00', 1, '0.00', 0, '0.00', 'equity', '150000.00', '0', '20.00', '12000.00', '0.00', '', '0', 6, '150000.00', '0', '5000.00', '12000.00', 60),
(11, 29, 'متجر زهراء', 'متجر إلكتروني متخصص في بيع منتجات طبيعية للعناية بالبشرة والشعر، يستهدف السيدات الباحثات عن بدائل صحية وآمنة بعيدًا عن المنتجات الكيميائية، مع توصيل سريع داخل المملكة.\r\n\r\n', '1088997654', 'business', 'متاجر إلكترونية', '', 'الشرقية', 'الخبر', 0, 0, 0, '0.00', 0, '0.00', 1, '0.00', 1, '0.00', 0, '0.00', 'loan', '0.00', '0', '0.00', '0.00', '100000.00', 'لتجهيز موقع إلكتروني وتوسيع المخزون', '0', 6, '100000.00', '0', '4000.00', '9000.00', 50),
(12, 30, 'تطبيق شِفت', 'تطبيق ذكي يربط بين أصحاب الأعمال الصغيرة والعاملين المؤقتين (شفتات) لتغطية الوظائف القصيرة الأجل في المطاعم والمقاهي والمحلات، مع نظام تقييم وجدولة مرن وفوري.', '1098765432', 'business', 'محلات', '', 'الوسطى', 'الرياض', 1, 0, 0, '0.00', 1, '0.00', 1, '0.00', 1, '0.00', 0, '0.00', 'equity', '200000.00', '0', '25.00', '25000.00', '0.00', '', '0', 6, '200000.00', '0', '0.00', '25000.00', 60),
(13, 31, 'مخبوزات سُكّر وهيل', 'مشروع منزلي لإنتاج وبيع المخبوزات والحلويات السعودية بنكهات مبتكرة، يتم البيع عبر إنستقرام وتطبيقات التوصيل، ويستهدف الأسر والموظفين ومحبي الطعم التراثي بلمسة عصرية.', '1100223344', 'business', 'محلات', '', 'الشرقية', 'الدمام', 0, 0, 0, '0.00', 0, '0.00', 1, '0.00', 1, '0.00', 0, '0.00', 'loan', '0.00', '0', '0.00', '0.00', '75000.00', 'لتجهيز معمل منزلي مصغر وتوفير معدات التعبئة والتغليف', '0', 6, '75000.00', '0', '3500.00', '8000.00', 50),
(14, 32, 'بوتيك لمسة نوف', 'متجر متخصص في الملابس النسائية الخليجية العصرية بتصاميم حصرية وجودة عالية. يقدم خيارات تفصيل حسب الطلب وتوصيل سريع داخل المملكة مع متجر إلكتروني تفاعلي.\r\n\r\n', '1122334455', 'business', 'محلات', '', 'الجنوبية', 'أبها', 1, 0, 1, '0.00', 1, '0.00', 1, '0.00', 1, '0.00', 0, '0.00', 'equity', '120000.00', '0', '22.00', '18000.00', '0.00', '', '0', 6, '120000.00', '0', '7000.00', '18000.00', 60),
(15, 33, 'وصلها', 'خدمة توصيل سريعة ومحلية داخل المدينة باستخدام سيارات مجهزة، تستهدف المتاجر الصغيرة، والمطاعم، والأفراد، مع تطبيق بسيط لتتبع الطلبات وحجز السائقين.', '1133557799', 'business', 'أخرى', '', 'الشرقية', 'الأحساء', 1, 0, 0, '0.00', 1, '0.00', 1, '0.00', 1, '0.00', 0, '0.00', 'equity', '90000.00', '0', '1.00', '15000.00', '0.00', '', '0', 6, '90000.00', '0', '6000.00', '15000.00', 60),
(16, 34, 'مَدرَسة بلس', 'منصة تعليمية تقدم دورات احترافية باللغة العربية في المهارات الرقمية، مثل البرمجة، التصميم، والذكاء الاصطناعي. تستهدف طلاب الجامعات والخريجين الجدد، مع نظام اختبارات وشهادات رقمية.\r\n\r\n', '1122668844', 'tech', 'تطبيقات', '', 'الوسطى', 'الرياض', 1, 0, 0, '0.00', 0, '0.00', 1, '0.00', 1, '0.00', 0, '0.00', 'equity', '180000.00', '0', '30.00', '20000.00', '0.00', '', '0', 6, '180000.00', '0', '0.00', '20000.00', 60),
(17, 35, 'نقش وسِف', 'مشروع حرفي نسائي مختص في إنتاج أعمال يدوية تراثية مثل السلال، النقش على الفخار، والتطريز النجدي، بلمسة عصرية وتسويق رقمي من خلال الإنستقرام والمعارض المحلية.', '1144772233', 'business', 'محلات', '', 'الجنوبية', 'نجران', 1, 0, 0, '0.00', 0, '0.00', 1, '0.00', 1, '0.00', 0, '0.00', 'loan', '0.00', '0', '0.00', '0.00', '60000.00', 'لشراء أدوات الحرف اليدوية وتوسيع ورشة الإنتاج', '0', 6, '60000.00', '0', '2800.00', '8500.00', 65),
(18, 36, 'بلو فيش 360 – مطعم تحت الماء افتراضيًا', 'مطعم بحري في جدة يجمع بين الأطباق البحرية الطازجة وتجربة غامرة بفضل شاشات بانورامية تعرض لك محيطًا تحت الماء وكأنك تتناول طعامك بين الشعاب المرجانية.\r\nمع كل طبق يُعرض لك فيديو قصير عن قصة الصياد أو طريقة اصطياد مستدامة.', '4037789120', 'business', 'مطاعم', '', 'الغربية', 'جدة', 1, 8, 1, '25000.00', 1, '70000.00', 1, '6000.00', 1, '3000.00', 1, '4000.00', 'equity', '350000.00', '0', '15.00', '110000.00', '0.00', '', '0', 6, '350000.00', '0', '30000.00', '110000.00', 70),
(19, 37, 'قرية الورق – حرف ورقية إبداعية', 'مشروع في أبها يصنع هدايا وقطع فنية من الورق المعاد تدويره بتقنيات نحت وطباعة ثلاثية الأبعاد، مع ورش عمل للأطفال والسياح. والهدف نشر ثقافة الاستدامة والفن في آن واحد.\r\n', '4037789210', 'business', 'أخرى', 'حرفي', 'الجنوبية', 'الطائف', 1, 5, 1, '12000.00', 1, '5000.00', 1, '8000.00', 1, '2000.00', 1, '2500.00', 'equity', '200000.00', '0', '16.00', '60000.00', '0.00', '', '0', 6, '200000.00', '0', '22000.00', '60000.00', 95),
(20, 38, 'تِك فارم – مزرعة ذكية حضارية', 'في قلب ابها، مزرعة ذكية تعتمد على الزراعة العمودية داخل مستودع تقني، تنتج خضروات عضوية وتبيعها عبر اشتراك شهري للعائلات والمطاعم.\r\n نظام مراقبة بالذكاء الاصطناعي يضبط الري والإضاءة للحصول على أفضل جودة.', '2159027744', 'business', 'أخرى', 'زراعي', 'الجنوبية', 'ابها', 1, 15, 0, '0.00', 1, '6000.00', 1, '8000.00', 1, '2000.00', 1, '3500.00', 'equity', '25000.00', '0', '18.00', '45000.00', '0.00', '', '0', 6, '25000.00', '0', '20000.00', '45000.00', 70),
(21, 39, 'لوجيستك برو – مركز خدمات لوجستية ذكي', 'مركز متكامل في ينبع يقدم خدمات التخزين، الشحن السريع، وتتبع البضائع باستخدام حلول إنترنت الأشياء (IoT) وبرمجيات متقدمة لإدارة المخزون، ويخدم شركات التجارة الإلكترونية والمتاجر المحلية.', '4023456789', 'logistics', 'مستودعات,نقل,شحن,خدمات لوجستية', '', 'الغربية', 'ينبع', 1, 7, 1, '14000.00', 1, '5000.00', 1, '10000.00', 1, '2000.00', 0, '0.00', 'equity', '450000.00', 'توسعة المستودعات، تطوير أنظمة التتبع الذكي، شراء معدات حديثة، وتغطية تكاليف التشغيل حتى الوصول لنقطة التعادل في الأرباح. التمويل يساعد على تسريع النمو واستقطاب عملاء جدد من قطاع التجارة الإلكترونية.', '18.00', '70000.00', '0.00', '', '0', 6, '450000.00', '0', '25000.00', '70000.00', 95),
(22, 40, 'سِكّة كلاود – كبسولات النوم الذكية فوق السحاب', 'محطة كبسولات نوم ذكية فعليًا في قلب النماص، تقدم للزوار والمسافرين تجربة استثنائية للنوم أو الراحة وسط الطبيعة البكر وأجواء السحب. توفر كبسولات مجهزة بالكامل (تكييف، إنترنت، شاشات ترفيه، إضاءة ذكية)، وحجز إلكتروني بالساعة أو الليلة، مع جلسات خارجية مطلة ومرافق استحمام فاخرة. المشروع قائم ومفتوح حاليًا للزوار طوال المواسم.', '3098812237', 'tourism', 'فنادق', '', 'الجنوبية', 'النماص', 1, 3, 1, '8000.00', 1, '6000.00', 1, '5500.00', 1, '2000.00', 0, '0.00', 'loan', '0.00', '0', '0.00', '0.00', '160000.00', 'رفع الطاقة الاستيعابية للمشروع بشراء كبسولات إضافية، تطوير نظام الحجز الإلكتروني، وتجهيز مرافق حديثة (استحمام – صالات انتظار مطلة)، إلى جانب حملة تسويق تستهدف السياح في النماص وجنوب المملكة.', '0', 24, '160000.00', '0', '11000.00', '25000.00', 95);

-- --------------------------------------------------------

--
-- بنية الجدول `project_profits`
--

CREATE TABLE `project_profits` (
  `id` int(11) NOT NULL,
  `investment_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `profit_date` date NOT NULL,
  `status` enum('available','withdrawn') DEFAULT 'available',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- بنية الجدول `project_updates`
--

CREATE TABLE `project_updates` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `investment_id` int(11) DEFAULT NULL,
  `update_text` text NOT NULL,
  `period` varchar(20) DEFAULT NULL,
  `profit_loss` decimal(12,2) DEFAULT NULL,
  `pdf_report` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `project_updates`
--

INSERT INTO `project_updates` (`id`, `project_id`, `investment_id`, `update_text`, `period`, `profit_loss`, `pdf_report`, `created_at`) VALUES
(1, 28, 24, 'هااي', NULL, NULL, NULL, '2025-08-01 10:47:41'),
(2, 35, 25, 'في الثلاث شهور الماضيه سويت كذا', NULL, NULL, NULL, '2025-08-01 19:40:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agreements`
--
ALTER TABLE `agreements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `investment_id` (`investment_id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `slug_2` (`slug`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `investor_id` (`investor_id`),
  ADD KEY `entrepreneur_id` (`entrepreneur_id`);

--
-- Indexes for table `entrepreneurs`
--
ALTER TABLE `entrepreneurs`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `national_id` (`national_id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `ideas`
--
ALTER TABLE `ideas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_entrepreneur_id` (`entrepreneur_id`);

--
-- Indexes for table `investments`
--
ALTER TABLE `investments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `investor_id` (`investor_id`),
  ADD KEY `entrepreneur_id` (`entrepreneur_id`);

--
-- Indexes for table `investors`
--
ALTER TABLE `investors`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `national_id` (`national_id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`);

--
-- Indexes for table `platform_ratings`
--
ALTER TABLE `platform_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_per_investment` (`investor_id`,`investment_id`),
  ADD KEY `investment_id` (`investment_id`),
  ADD KEY `investor_id` (`investor_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entrepreneur_id` (`entrepreneur_id`);

--
-- Indexes for table `project_profits`
--
ALTER TABLE `project_profits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `investment_id` (`investment_id`);

--
-- Indexes for table `project_updates`
--
ALTER TABLE `project_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agreements`
--
ALTER TABLE `agreements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `entrepreneurs`
--
ALTER TABLE `entrepreneurs`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `ideas`
--
ALTER TABLE `ideas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `investments`
--
ALTER TABLE `investments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `investors`
--
ALTER TABLE `investors`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `platform_ratings`
--
ALTER TABLE `platform_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `project_profits`
--
ALTER TABLE `project_profits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_updates`
--
ALTER TABLE `project_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- قيود الجداول المحفوظة
--

--
-- القيود للجدول `agreements`
--
ALTER TABLE `agreements`
  ADD CONSTRAINT `agreements_ibfk_1` FOREIGN KEY (`investment_id`) REFERENCES `investments` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`investor_id`) REFERENCES `investors` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_3` FOREIGN KEY (`entrepreneur_id`) REFERENCES `entrepreneurs` (`ID`) ON DELETE CASCADE;

--
-- القيود للجدول `ideas`
--
ALTER TABLE `ideas`
  ADD CONSTRAINT `fk_entrepreneur_id` FOREIGN KEY (`entrepreneur_id`) REFERENCES `entrepreneurs` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- القيود للجدول `investments`
--
ALTER TABLE `investments`
  ADD CONSTRAINT `investments_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `investments_ibfk_2` FOREIGN KEY (`investor_id`) REFERENCES `investors` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `investments_ibfk_3` FOREIGN KEY (`entrepreneur_id`) REFERENCES `entrepreneurs` (`ID`) ON DELETE CASCADE;

--
-- القيود للجدول `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `platform_ratings`
--
ALTER TABLE `platform_ratings`
  ADD CONSTRAINT `fk_pr_investment` FOREIGN KEY (`investment_id`) REFERENCES `investments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pr_investor` FOREIGN KEY (`investor_id`) REFERENCES `investors` (`ID`) ON DELETE CASCADE;

--
-- القيود للجدول `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`entrepreneur_id`) REFERENCES `entrepreneurs` (`ID`) ON DELETE CASCADE;

--
-- القيود للجدول `project_profits`
--
ALTER TABLE `project_profits`
  ADD CONSTRAINT `project_profits_ibfk_1` FOREIGN KEY (`investment_id`) REFERENCES `investments` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `project_updates`
--
ALTER TABLE `project_updates`
  ADD CONSTRAINT `project_updates_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
