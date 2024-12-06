<?php
session_start();

$servername = "localhost";
$username = "srikrishnaDeveloper";
$password = "leads@123";
$database = "srikrishnaDeveloper";

// Connection setup
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$correct_username = "skd";
$correct_password = "skd@123";

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle login
if (isset($_POST['username']) && isset($_POST['password'])) {
    if ($_POST['username'] == $correct_username && $_POST['password'] == $correct_password) {
        $_SESSION['loggedin'] = true;
    } else {
        $login_error = "Invalid username or password.";
    }
}

// Check if logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>LEADS Dashboard</title>
        <link rel="icon" type="image/png" href="img/side-logo.png">
        <link rel="stylesheet" href="thankyou.css">
    </head>
    <style>
        .addCategory {

    padding: 4rem;
    padding-top: 15vh;

}

.container {
    max-width: 1000px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

 .forminput{
    margin-right: 10px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 3px;

 }

 .formlabel{
    margin-right: 10px;
    font-weight: bold;
 }
.formbutton{
    padding: 10px;
    background-color: #006F6F;
    color: #fff;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    margin-left: 10px;
    width: 20%;
}
.formbutton:hover{
    background-color: #012101;
}
.formss {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 100%;
    margin: 0 auto 20px auto;
    padding: 20px;
    background-color: #f2f2f2;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
.logout-form {
     background-color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 13%;
    margin: 10px auto 17px auto;
    padding: 19px;
    /* background-color: #f2f2f2; */
    border-radius: 5px;
    margin-left: 2rem;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
.logout-form input[type="submit"] {
    padding: 10px 20px;
    background-color: #006F6F;
    color: #fff;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}
.logout-form input[type="submit"]:hover {
    background-color: #012101;
}

h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}
/*  */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    padding: 12px;
    text-align: left;
    border: 1px solid #ddd;
}
th {
    background-color: #f2f2f2;
    color: #333;
}
tr:hover {
    background-color: #f9f9f9;
}
.button {
    padding: 12px 24px;
    background-color:#006F6F;
    border: 1px solid #33553d;
    border-radius: 3px;
    color: #fff;
    cursor: pointer;
    margin-top: 20px;
}

button {
    /*margin: 50px auto;*/
}

#date-submit {
    background-color:#006F6F;
    color: white;
    font-size: 1rem;
    padding: 0.4rem 1rem;
    border: 0;
    margin-left: 2rem;
}

#btnExport {
    background-color: #006F6F;
    color: white;
    font-size: 1rem;
    padding: 16px 1.5rem;
    border: 0;
    margin: 3rem 0;
}

.lead-access-manu {
    z-index: -1 !important;
    display: none !important;
}
    </style>
    <body>
    <?php include "head.php"; ?>
    <div class="container">
        <h1>Welcome to LEADS Dashboard</h1>
        <form class="logout-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
            <input type="hidden" name="logout" value="true">
            <input type="submit" value="Logout" >
        </form>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" class="formss">
            <label for="startfilter" class="formlabel">From:</label>
            <input type="date" id="startfilter" name="startfilter" class="forminput" required>
            <label class="formlabel" for="endfilter">To:</label>
            <input type="date" id="endfilter" class="forminput" name="endfilter" required>
            <input type="submit" class="formbutton" value="Filter">
             <button class="formbutton" id="btnExport" onclick="downloadExcel();">Download</button>
        </form>

      
     <table>
    <!--<thead>-->
    <!--    <tr>-->
    <!--        <th>ID</th>-->
    <!--        <th>Name</th>-->
    <!--        <th>Email</th>-->
    <!--        <th>Country Code</th>-->
    <!--        <th>Mobile</th>-->
    <!--        <th>Date</th>-->
    <!--    </tr>-->
    <!--</thead>-->
   <?php
// Base SQL query
$sql = "SELECT * FROM `krishe_avya`";

// Prepare statement
$stmt = $conn->prepare($sql);

// Check for date filters
if (!empty($_GET['startfilter']) && !empty($_GET['endfilter'])) {
    $start_date = $_GET['startfilter'];
    $end_date = $_GET['endfilter'];

    // Adjust SQL query for DATE column
    $sql .= " WHERE created_at BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
} else {
    $stmt = $conn->prepare($sql);
}

// Execute the query
$stmt->execute();
$result = $stmt->get_result();

// Debugging
if (!$result) {
    error_log("SQL Error: " . $conn->error);
}
?>

<table id="headerTable">
    <thead>
        <tr>
            <th>S.No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Country Code</th>
            <th>Number</th>
          
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            $serial = 1;
            while ($row = $result->fetch_assoc()) {
                ?>
                <tr>
                    <td><?= $serial++ ?></td>
                    <td><?= htmlspecialchars($row['Name']) ?></td>
                    <td><?= htmlspecialchars($row['Email']) ?></td>
                    <td><?= htmlspecialchars($row['country_code'])?></td>
                    <td><?= htmlspecialchars($row['Mobile']) ?></td>
                   
                  <td><?= date('Y-m-d', strtotime($row['Date'])) ?></td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='7'>NO RECORDS FOUND</td></tr>";
        }
        ?>
    </tbody>
</table>

<iframe id="txtArea1" style="display:none"></iframe>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
    
    function downloadExcel() {
        // Get the table element
        const table = document.querySelector('table');
        
        // Convert table data to a worksheet
        const workbook = XLSX.utils.table_to_book(table, { sheet: "krisheavya Leads" });
        
        // Export to Excel file
        XLSX.writeFile(workbook, 'krisheavya.xlsx');
    }
        // async function downloadPDF() {
        //     const { jsPDF } = window.jspdf;
        //     const doc = new jsPDF();
        //     doc.text("Leads Data", 14, 16);
        //     doc.autoTable({
        //         html: 'table',
        //         startY: 22,
        //         styles: {
        //             fontSize: 10,
        //             lineColor: [0, 0, 0],
        //             lineWidth: 0.1
        //         }
        //     });
        //     doc.save('leads-data.pdf');
        // }
    </script>
    </body>
    </html>
    <?php
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="thankyou.css">
        <title>Login</title>
    </head>
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
}

.lead-access-container {
    max-width: 400px;
    margin: 50px auto;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.access-logo img {
    max-width: 150px;
    margin-bottom: 20px;
}

form {
    display: flex;
    flex-direction: column;
    align-items: center;
}

form label {
    width: 100%;
    text-align: left;
    margin: 10px 0 5px;
    font-weight: bold;
    color: #333;
}

form input[type="text"],
form input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
    box-sizing: border-box;
}

form input[type="submit"] {
    width: 100%;
    padding: 10px;
    background-color: #677266;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
    margin-top: 10px;
}

form input[type="submit"]:hover {
    background-color: #505c50;
}

form p {
    margin: 10px 0 0;
    font-size: 0.9rem;
    color: #d9534f;
}

@media (max-width: 480px) {
    .lead-access-container {
        margin: 20px;
        padding: 15px;
    }

    form label {
        font-size: 0.9rem;
    }

    form input[type="submit"] {
        font-size: 0.9rem;
    }
}


    </style>
    <body>
    <?php include "head.php"; ?>
    <section class="lead-access">
        <div class="lead-access-container">
            <div class="access-logo">
                <img src="img/side-logo.png" alt="Logo">
            </div>
            <?php if (isset($login_error)) echo "<p style='color:red;'>$login_error</p>"; ?>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required placeholder="Username">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required placeholder="Password">
                <input type="submit" value="Login" style="background-color:#677266; color:#fff">
            </form>
        </div>
    </section>
    </body>
    </html>
    <?php
}
?>
