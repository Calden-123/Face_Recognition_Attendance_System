<?php
$courseCode = isset($_GET['course']) ? $_GET['course'] : '';
$unitCode = isset($_GET['unit']) ? $_GET['unit'] : '';

$studentRows = fetchStudentRecordsFromDatabase($courseCode, $unitCode);

$coursename = "";
if (!empty($courseCode)) {
    $coursename_query = "SELECT name FROM tblcourse WHERE courseCode = '$courseCode'";
    $result = fetch($coursename_query);
    foreach ($result as $row) {
        $coursename = $row['name'];
    }
}
$unitname = "";
if (!empty($unitCode)) {
    $unitname_query = "SELECT name FROM tblunit WHERE unitCode = '$unitCode'";
    $result = fetch($unitname_query);
    foreach ($result as $row) {
        $unitname = $row['name'];
    }
}

// JavaScript data for the dropdowns
$courseToUnits = [
    "BINCT" => ["CS101", "CS102", "CS103", "CS104", "CS105"],
    "DIIAD" => ["CS103", "CS104"],
    "DIIBA" => ["CS101", "CS103"]
];

$unitNames = [
    "CS101" => "Introduction to Computer Science",
    "CS102" => "Data Structures",
    "CS103" => "Database Systems",
    "CS104" => "Web Development",
    "CS105" => "Artificial Intelligence"
];

// Function to calculate attendance mark based on percentage
function calculateAttendanceMark($presentCount, $totalLectures) {
    // Add validation to prevent NaN errors
    if ($totalLectures == 0 || !is_numeric($presentCount) || !is_numeric($totalLectures)) {
        return 0;
    }
    
    // Ensure values are integers
    $presentCount = (int)$presentCount;
    $totalLectures = (int)$totalLectures;
    
    $attendancePercentage = ($presentCount / $totalLectures) * 100;
    
    if ($attendancePercentage < 50) {
        return 0; // Strong penalty for very poor attendance
    } elseif ($attendancePercentage >= 75) {
        return 100; // Full reward for good attendance
    } else {
        return 70; // Flat 70% for 50-74% attendance
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="resources/images/logo/attnlg.png" rel="icon">
    <title>lecture Dashboard</title>
    <link rel="stylesheet" href="resources/assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/topbar.php'; ?>
    <section class="main">
        <div class="main--content">
            <form class="lecture-options" id="selectForm" method="GET" action="">
                <select required name="course" id="courseSelect" onChange="updateUnits()">
                    <option value="" <?php echo empty($courseCode) ? 'selected' : ''; ?>>Select Course</option>
                    <option value="BINCT" <?php echo ($courseCode == 'BINCT') ? 'selected' : ''; ?>>BACHELOR OF INFORMATION AND COMMUNICATIONS TECHNOLOGY</option>
                    <option value="DIIAD" <?php echo ($courseCode == 'DIIAD') ? 'selected' : ''; ?>>DIPLOMA IN INFORMATION AND COMMUNICATION TECHNOLOGY IN APPLICATIONS DEVELOPMENT</option>
                    <option value="DIIBA" <?php echo ($courseCode == 'DIIBA') ? 'selected' : ''; ?>>Diploma in Information and Communication Technology in Business Analysis</option>
                </select>

                <select required name="unit" id="unitSelect" onChange="this.form.submit()">
                    <option value="" selected disabled>Select Module</option>
                    <?php
                    if (!empty($courseCode) && isset($courseToUnits[$courseCode])) {
                        foreach ($courseToUnits[$courseCode] as $unit) {
                            $isSelected = ($unit == $unitCode) ? 'selected' : '';
                            $displayText = $unit . " - " . $unitNames[$unit];
                            echo "<option value='$unit' $isSelected>$displayText</option>";
                        }
                    }
                    ?>
                </select>
            </form>

            <?php if (!empty($courseCode) && !empty($unitCode)): ?>
                <button class="add" onclick="exportTableToExcel('attendaceTable', '<?php echo $unitCode ?>_on_<?php echo date('Y-m-d'); ?>','<?php echo $coursename ?>', '<?php echo $unitname ?>')">Export Attendance As Excel</button>
            <?php endif; ?>

            <?php if (!empty($courseCode) && !empty($unitCode)): ?>
                <div class="table-container">
                    <div class="title">
                        <h2 class="section--title">Attendance Preview</h2>
                    </div>
                    <div class="table attendance-table" id="attendaceTable">
                        <table>
                            <thead>
                                <tr>
                                    <th>Registration No</th>
                                    <?php
                                    $distinctDatesQuery = "SELECT DISTINCT dateMarked FROM tblattendance WHERE course = :courseCode AND unit = :unitCode";
                                    $stmtDates = $pdo->prepare($distinctDatesQuery);
                                    $stmtDates->execute([
                                        ':courseCode' => $courseCode,
                                        ':unitCode' => $unitCode,
                                    ]);
                                    $distinctDatesResult = $stmtDates->fetchAll(PDO::FETCH_ASSOC);
                                    $totalLectures = count($distinctDatesResult);

                                    if ($distinctDatesResult) {
                                        foreach ($distinctDatesResult as $dateRow) {
                                            echo "<th>" . $dateRow['dateMarked'] . "</th>";
                                        }
                                        echo "<th>Attendance Mark</th>";
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $studentsQuery = "SELECT DISTINCT studentRegistrationNumber FROM tblattendance WHERE course = :courseCode AND unit = :unitCode";
                                $stmtStudents = $pdo->prepare($studentsQuery);
                                $stmtStudents->execute([
                                    ':courseCode' => $courseCode,
                                    ':unitCode' => $unitCode,
                                ]);
                                $studentRows = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($studentRows as $row) {
                                    $presentCount = 0;
                                    echo "<tr>";
                                    echo "<td>" . $row['studentRegistrationNumber'] . "</td>";

                                    foreach ($distinctDatesResult as $dateRow) {
                                        $date = $dateRow['dateMarked'];

                                        $attendanceQuery = "SELECT attendanceStatus FROM tblattendance 
                                        WHERE studentRegistrationNumber = :studentRegistrationNumber 
                                        AND dateMarked = :date 
                                        AND course = :courseCode 
                                        AND unit = :unitCode";
                                        $stmtAttendance = $pdo->prepare($attendanceQuery);
                                        $stmtAttendance->execute([
                                            ':studentRegistrationNumber' => $row['studentRegistrationNumber'],
                                            ':date' => $date,
                                            ':courseCode' => $courseCode,
                                            ':unitCode' => $unitCode,
                                        ]);
                                        $attendanceResult = $stmtAttendance->fetch(PDO::FETCH_ASSOC);

                                       if ($attendanceResult) {
    $status = $attendanceResult['attendanceStatus'];
    echo "<td>" . $status . "</td>";
    
    // More robust check for attendance status
    $normalizedStatus = strtolower(trim($status));
    if ($normalizedStatus === 'present' || $normalizedStatus === 'presence' || $normalizedStatus === 'presente') {
        $presentCount++;
        echo "<!-- DEBUG: Counted as present -->";
    } else {
        echo "<!-- DEBUG: Not counted: $status -->";
    }
} else {
    echo "<td>Absent</td>";
    echo "<!-- DEBUG: No attendance result -->";
}
                                    }
                                    
                                    // Calculate and display attendance mark
                                    $attendanceMark = calculateAttendanceMark($presentCount, $totalLectures);
                                    echo "<td>" . $attendanceMark . "%</td>";
                                    
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        // JavaScript for the dynamic unit dropdown
        const courseToUnits = {
            "BINCT": ["CS101", "CS102", "CS103", "CS104", "CS105"],
            "DIIAD": ["CS103", "CS104"],
            "DIIBA": ["CS101", "CS103"]
        };

        const unitNames = {
            "CS101": "Introduction to Computer Science",
            "CS102": "Data Structures",
            "CS103": "Database Systems",
            "CS104": "Web Development",
            "CS105": "Artificial Intelligence"
        };

        function updateUnits() {
            const courseSelect = document.getElementById('courseSelect');
            const selectedCourse = courseSelect.value;
            const unitSelect = document.getElementById('unitSelect');

            // Reset unit dropdown
            unitSelect.innerHTML = '<option value="" selected disabled>Select Module</option>';

            if (!selectedCourse) return;
            

            const unitsForCourse = courseToUnits[selectedCourse];

            unitsForCourse.forEach(unitCode => {
                const option = document.createElement('option');
                option.value = unitCode;
                option.textContent = `${unitCode} - ${unitNames[unitCode]}`;
                unitSelect.appendChild(option);
            });
            
            // Auto-submit the form when course is changed to update the unit dropdown with proper selected value
            document.getElementById('selectForm').submit();
        }

function exportTableToExcel(tableId, filename = '', courseCode = '', unitCode = '') {
    var table = document.getElementById(tableId);
    var currentDate = new Date();
    var formattedDate = currentDate.toLocaleDateString();

    var headerContent = '<p style="font-weight:700;"> Attendance for : ' + courseCode + ' Unit name : ' + unitCode + ' On: ' + formattedDate + '</p>';
    var tbody = document.createElement('tbody');
    var additionalRow = tbody.insertRow(0);
    var additionalCell = additionalRow.insertCell(0);
    additionalCell.innerHTML = headerContent;
    table.insertBefore(tbody, table.firstChild);
    
    // Convert table to workbook
    var wb = XLSX.utils.table_to_book(table, {
        sheet: "Attendance"
    });
    
    // Get the worksheet
    var ws = wb.Sheets["Attendance"];
    
    // Get the range of the worksheet
    var range = XLSX.utils.decode_range(ws['!ref']);
    
    // Format the Attendance Mark column (last column) as percentage
    var attendanceMarkColumn = range.e.c; // Last column index
    
    for (let R = range.s.r + 1; R <= range.e.r; ++R) { // Start from row 1 (skip headers)
        const cellAddress = XLSX.utils.encode_cell({r: R, c: attendanceMarkColumn});
        if (ws[cellAddress] && ws[cellAddress].v !== undefined) {
            // Update cell format to percentage
            ws[cellAddress].t = 'n'; // number type
            ws[cellAddress].z = '0%'; // percentage format
        }
    }

    var wbout = XLSX.write(wb, {
        bookType: 'xlsx',
        bookSST: true,
        type: 'binary'
    });
    var blob = new Blob([s2ab(wbout)], {
        type: 'application/octet-stream'
    });
    if (!filename.toLowerCase().endsWith('.xlsx')) {
        filename += '.xlsx';
    }

    saveAs(blob, filename);
}

        function s2ab(s) {
            var buf = new ArrayBuffer(s.length);
            var view = new Uint8Array(buf);
            for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
            return buf;
        }
    </script>

    <?php js_asset(['min/js/filesaver', 'min/js/xlsx', 'active_link']) ?>
</body>
</html>