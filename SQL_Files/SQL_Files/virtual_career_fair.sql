-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: May 01, 2026 at 02:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `virtual_career_fair`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `Admin_ID` int(3) NOT NULL,
  `Admin_Code` varchar(10) NOT NULL,
  `Privilege_Level` enum('SuperAdmin','Manager','Editor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `Company_ID` int(3) NOT NULL,
  `Company_Name` varchar(50) NOT NULL,
  `Location` varchar(900) NOT NULL,
  `Industry` varchar(50) NOT NULL,
  `Website_URL` varchar(100) NOT NULL,
  `Approved_By_Admin` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fair`
--

CREATE TABLE `fair` (
  `Fair_ID` int(3) NOT NULL,
  `Fair_Title` varchar(50) NOT NULL,
  `Status` varchar(50) NOT NULL,
  `Start_Date` datetime NOT NULL,
  `End_Date` datetime NOT NULL,
  `Approved_By_Admin` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interview`
--

CREATE TABLE `interview` (
  `Interview_ID` int(3) NOT NULL,
  `Student_ID` int(3) NOT NULL,
  `Recruiter_ID` int(3) NOT NULL,
  `Interview_Date` datetime NOT NULL,
  `Duration` int(11) NOT NULL,
  `Status` varchar(50) NOT NULL,
  `Feedback_Tags` text NOT NULL,
  `Meeting_Link` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job`
--

CREATE TABLE `job` (
  `Job_ID` int(3) NOT NULL,
  `Company_ID` int(3) NOT NULL,
  `Job_Title` varchar(50) NOT NULL,
  `Department` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `Job_ID` int(3) NOT NULL,
  `Student_ID` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `Sender_ID` int(3) NOT NULL,
  `Reciever_ID` int(3) NOT NULL,
  `Status` enum('Sent','Delivered','Read','Pending','Deleted','Failed') DEFAULT NULL,
  `Time_Stamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `Note_ID` int(11) NOT NULL,
  `Recruiter_ID` int(11) NOT NULL,
  `Student_ID` int(11) NOT NULL,
  `Content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `participates_in`
--

CREATE TABLE `participates_in` (
  `Company_ID` int(3) NOT NULL,
  `Fair_ID` int(3) NOT NULL,
  `.Booth_No` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE `queue` (
  `Queue_ID` int(3) NOT NULL,
  `Job_ID` int(3) NOT NULL,
  `Wait_Time` int(11) NOT NULL,
  `.Position_No` int(3) NOT NULL,
  `Status` enum('Waiting','In Progress','Finished','') NOT NULL,
  `.Booth_No` varchar(10) NOT NULL,
  `Company_ID` int(11) DEFAULT NULL,
  `Entry_Time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recruiter`
--

CREATE TABLE `recruiter` (
  `Recruiter_ID` int(3) NOT NULL,
  `Company_ID` int(11) NOT NULL,
  `Position` varchar(50) NOT NULL,
  `Phone_No.` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `required_skills`
--

CREATE TABLE `required_skills` (
  `Job_ID` int(3) NOT NULL,
  `Skill_Name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `Student_ID` int(3) NOT NULL,
  `Major` varchar(50) NOT NULL,
  `Resume_URL` varchar(200) NOT NULL,
  `GPA` decimal(3,2) NOT NULL,
  `Class_Year` year(4) NOT NULL,
  `Bio` varchar(500) DEFAULT NULL,
  `Ready_Status` enum('Available','In-Chat','Offline') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_skills`
--

CREATE TABLE `student_skills` (
  `Student_ID` int(3) NOT NULL,
  `Skill_Name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `User_ID` int(3) NOT NULL,
  `F_Name` varchar(50) NOT NULL,
  `L_Name` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` varchar(50) NOT NULL,
  `Status` varchar(50) NOT NULL,
  `Created_At` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `waits_in`
--

CREATE TABLE `waits_in` (
  `Student_ID` int(3) NOT NULL,
  `Queue_ID` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Admin_ID`),
  ADD KEY `User_ID` (`Admin_ID`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`Company_ID`);

--
-- Indexes for table `fair`
--
ALTER TABLE `fair`
  ADD PRIMARY KEY (`Fair_ID`),
  ADD KEY `approves` (`Approved_By_Admin`);

--
-- Indexes for table `interview`
--
ALTER TABLE `interview`
  ADD PRIMARY KEY (`Interview_ID`),
  ADD KEY `has` (`Student_ID`),
  ADD KEY `make` (`Recruiter_ID`);

--
-- Indexes for table `job`
--
ALTER TABLE `job`
  ADD PRIMARY KEY (`Job_ID`),
  ADD KEY `offers` (`Company_ID`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`Job_ID`,`Student_ID`),
  ADD KEY `match` (`Student_ID`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`Sender_ID`,`Reciever_ID`),
  ADD KEY `recieves` (`Reciever_ID`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`Note_ID`),
  ADD KEY `about` (`Student_ID`),
  ADD KEY `makes` (`Recruiter_ID`);

--
-- Indexes for table `participates_in`
--
ALTER TABLE `participates_in`
  ADD PRIMARY KEY (`Company_ID`,`Fair_ID`),
  ADD KEY `doneBy` (`Fair_ID`);

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
  ADD PRIMARY KEY (`Queue_ID`),
  ADD KEY `fk` (`Company_ID`),
  ADD KEY `fkk` (`Job_ID`);

--
-- Indexes for table `recruiter`
--
ALTER TABLE `recruiter`
  ADD PRIMARY KEY (`Recruiter_ID`),
  ADD KEY `represents` (`Company_ID`);

--
-- Indexes for table `required_skills`
--
ALTER TABLE `required_skills`
  ADD PRIMARY KEY (`Job_ID`,`Skill_Name`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`Student_ID`);

--
-- Indexes for table `student_skills`
--
ALTER TABLE `student_skills`
  ADD PRIMARY KEY (`Student_ID`,`Skill_Name`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `waits_in`
--
ALTER TABLE `waits_in`
  ADD PRIMARY KEY (`Student_ID`,`Queue_ID`),
  ADD KEY `waits` (`Queue_ID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `i` FOREIGN KEY (`Admin_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fair`
--
ALTER TABLE `fair`
  ADD CONSTRAINT `approves` FOREIGN KEY (`Approved_By_Admin`) REFERENCES `admin` (`Admin_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `interview`
--
ALTER TABLE `interview`
  ADD CONSTRAINT `has` FOREIGN KEY (`Student_ID`) REFERENCES `student` (`Student_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `make` FOREIGN KEY (`Recruiter_ID`) REFERENCES `recruiter` (`Recruiter_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job`
--
ALTER TABLE `job`
  ADD CONSTRAINT `offers` FOREIGN KEY (`Company_ID`) REFERENCES `company` (`Company_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `match` FOREIGN KEY (`Student_ID`) REFERENCES `student` (`Student_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `matches` FOREIGN KEY (`Job_ID`) REFERENCES `job` (`Job_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `recieves` FOREIGN KEY (`Reciever_ID`) REFERENCES `student` (`Student_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sends` FOREIGN KEY (`Sender_ID`) REFERENCES `recruiter` (`Recruiter_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `about` FOREIGN KEY (`Student_ID`) REFERENCES `student` (`Student_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `makes` FOREIGN KEY (`Recruiter_ID`) REFERENCES `recruiter` (`Recruiter_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `participates_in`
--
ALTER TABLE `participates_in`
  ADD CONSTRAINT `doneBy` FOREIGN KEY (`Fair_ID`) REFERENCES `fair` (`Fair_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `participatesIn` FOREIGN KEY (`Company_ID`) REFERENCES `company` (`Company_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `queue`
--
ALTER TABLE `queue`
  ADD CONSTRAINT `fk` FOREIGN KEY (`Company_ID`) REFERENCES `company` (`Company_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkk` FOREIGN KEY (`Job_ID`) REFERENCES `job` (`Job_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `recruiter`
--
ALTER TABLE `recruiter`
  ADD CONSTRAINT `inherits` FOREIGN KEY (`Recruiter_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `represents` FOREIGN KEY (`Company_ID`) REFERENCES `company` (`Company_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `required_skills`
--
ALTER TABLE `required_skills`
  ADD CONSTRAINT `requires` FOREIGN KEY (`Job_ID`) REFERENCES `job` (`Job_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `inherit` FOREIGN KEY (`Student_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_skills`
--
ALTER TABLE `student_skills`
  ADD CONSTRAINT `have` FOREIGN KEY (`Student_ID`) REFERENCES `student` (`Student_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `waits_in`
--
ALTER TABLE `waits_in`
  ADD CONSTRAINT `waits` FOREIGN KEY (`Queue_ID`) REFERENCES `queue` (`Queue_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `waitsIn` FOREIGN KEY (`Student_ID`) REFERENCES `student` (`Student_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
