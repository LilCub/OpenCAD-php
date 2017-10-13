<?php

/**
Open source CAD system for RolePlaying Communities.
Copyright (C) 2017 Shane Gill

This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

This program comes with ABSOLUTELY NO WARRANTY; Use at your own risk.
**/

include("../oc-config.php");

/* Handle POST requests */
if (isset($_POST['delete_name'])){
    delete_name();
}
if (isset($_POST['delete_plate'])){
    delete_plate();
}
if (isset($_POST['create_name'])){
    create_name();
}
if (isset($_POST['create_plate'])){
    create_plate();
}


function ncicGetNames()
{
    $uid = $_SESSION['id'];

    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link) {
        die('Could not connect: ' .mysql_error());
    }

    $query = 'SELECT ncic_names.* FROM `ncic_names` WHERE ncic_names.submittedById = "' . $uid . '"';

    $result=mysqli_query($link, $query);

    $num_rows = $result->num_rows;

    if($num_rows == 0)
    {
        echo "<div class=\"alert alert-info\"><span>You currently have no identities</span></div>";
    }
    else
    {
        echo '
            <table id="ncic_names" class="table table-striped table-bordered">
            <thead>
                <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>DOB</th>
                <th>Address</th>
                <th>Sex</th>
                <th>Race</th>
                <th>DL Status</th>
                <th>Hair Color</th>
                <th>Build</th>
                <th>Actions</th>
                </tr>
            </thead>
            <tbody>
        ';

        while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
        {
            echo '
            <tr>
                <td>'.$row[3].'</td>
                <td>'.$row[4].'</td>
                <td>'.$row[5].'</td>
                <td>'.$row[6].'</td>
                <td>'.$row[7].'</td>
                <td>'.$row[8].'</td>
                <td>'.$row[9].'</td>
                <td>'.$row[10].'</td>
                <td>'.$row[11].'</td>
                <td>
                    <button name="edit_name" data-toggle="modal" data-target="#editNameModal" class="btn btn-xs btn-link" disabled>Edit</button>
                    <form action="../actions/civActions.php" method="post">
                    <input name="delete_name" type="submit" class="btn btn-xs btn-link" style="color: red;" value="Delete"/>
                    <input name="uid" type="hidden" value='.$row[0].' />
                    </form>
                </td>
            </tr>
            ';
        }

        echo '
            </tbody>
            </table>
        ';
    }
}

function ncicGetPlates()
{

    $uid = $_SESSION['id'];

    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link) {
        die('Could not connect: ' .mysql_error());
    }

    $query = 'SELECT ncic_plates.*, ncic_names.first_name, ncic_names.last_name FROM ncic_plates INNER JOIN ncic_names ON ncic_names.id=ncic_plates.name_id WHERE ncic_plates.user_id = "' . $uid . '"';

    $result=mysqli_query($link, $query);

    $num_rows = $result->num_rows;

    if($num_rows == 0)
    {
        echo "<div class=\"alert alert-info\"><span>You currently have no vehicles</span></div>";
    }
    else
    {
        echo '
            <table id="ncic_plates" class="table table-striped table-bordered">
            <thead>
                <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Plate</th>
                <th>Reg. State</th>
                <th>Make</th>
                <th>Model</th>
                <th>Color</th>
                <th>Ins. Status</th>
                <th>Flags</th>
                <th>Notes</th>
                <th>Actions</th>
                </tr>
            </thead>
            <tbody>
        ';

        while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
        {

            echo '
            <tr>
                <td>'.$row[11].'</td>
                <td>'.$row[12].'</td>
                <td>'.$row[2].'</td>
                <td>'.$row[8].'</td>
                <td>'.$row[3].'</td>
                <td>'.$row[4].'</td>
                <td>'.$row[5].'</td>
                <td>'.$row[6].'</td>
                <td>'.$row[7].'</td>
                <td>'.$row[9].'</td>
                <td>
                    <form action="../actions/civActions.php" method="post">
                    <input name="approveUser" type="submit" class="btn btn-xs btn-link" value="Edit" disabled />
                    <input name="delete_plate" type="submit" class="btn btn-xs btn-link" style="color: red;" value="Delete" enabled/>
                    <input name="vehid" type="hidden" value='.$row[0].' />
                    </form>
                </td>
            </tr>
            ';
        }

        echo '
            </tbody>
            </table>
        ';
    }
}


function getCivilianNamesOption()
{
    session_start();

    $uid = $_SESSION['id'];

    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link) {
        die('Could not connect: ' .mysql_error());
    }

    $sql = 'SELECT id, first_name, last_name FROM ncic_names WHERE civilian_names.user_id = "' . $uid . '"';

    $result=mysqli_query($link, $sql);

    while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
    {
        echo "<option value=".$row[0].">".$row[1]." ".$row[2]."</option>";
    }
}

function getCivilianNames()
{
    session_start();


    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

	$sql = 'SELECT ncic_names.id, ncic_names.first_name, ncic_names.last_name FROM ncic_names';

	$result=mysqli_query($link, $sql);

	while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
	{
		echo "<option value=\"$row[0]\">$row[1] $row[2]</option>";
	}
	mysqli_close($link);
}


function delete_name()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

    $uid = $_POST['uid'];

    $query = "DELETE FROM ncic_names WHERE id = ?";

    try {
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "i", $uid);
        $result = mysqli_stmt_execute($stmt);

        if ($result == FALSE) {
            die(mysqli_error($link));
        }
    }
    catch (Exception $e)
    {
        die("Failed to run query: " . $e->getMessage());
    }

    session_start();
    $_SESSION['nameMessage'] = '<div class="alert alert-success"><span>Successfully removed civilian name</span></div>';
    header("Location: ../civilian.php");
}

function delete_plate()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

    $vehid = $_POST['vehid'];

    $query = "DELETE FROM ncic_plates WHERE id = ?";

    try {
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "i", $vehid);
        $result = mysqli_stmt_execute($stmt);

        if ($result == FALSE) {
            die(mysqli_error($link));
        }
    }
    catch (Exception $e)
    {
        die("Failed to run query: " . $e->getMessage());
    }

    session_start();
    $_SESSION['plateMessage'] = '<div class="alert alert-success"><span>Successfully removed civilian plate</span></div>';
    header("Location: ../civilian.php");
}

function create_name()
{
    session_start();

    $fullName = $_POST['civNameReq'];
    $firstName = explode(" ", $fullName) [0];
    $lastName = explode(" ", $fullName) [1];

    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link)
    {
        die('Could not connect: ' . mysql_error());
    }

    $query = 'SELECT first_name, last_name FROM ncic_names WHERE first_name = "' . $firstName . '" AND last_name = "' . $lastName . '"';

    $result = mysqli_query($link, $query);

    $num_rows = $result->num_rows;

    if (!$num_rows == 0)
    {
        $_SESSION['identityMessage'] = '<div class="alert alert-danger"><span>Name already exists</span></div>';

        sleep(1);
        header("Location:../civilian.php");
    }

    // If name doesn't exist, add it to ncic_requests table
    //Who submitted it
    $submittedByName = $_SESSION['name'];
    $submitttedById = $_SESSION['id'];
    //Submission Data
    $firstName;
    $lastName;
    $dob = $_POST['civDobReq'];
    $address = $_POST['civAddressReq'];
    $sex = $_POST['civSexReq'];
    $race = $_POST['civRaceReq'];
	$dlstatus = $_POST['civDL'];
    $hair = $_POST['civHairReq'];
    $build = $_POST['civBuildReq'];

    $query = "INSERT INTO ncic_names (submittedByName, submittedById, first_name, last_name, dob, address, sex, race, dl_status, hair_color, build)
    VALUES (?,?,?,?,?,?,?,?,?,?,?)";

    try
    {
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "sssssssssss", $submittedByName, $submitttedById, $firstName, $lastName, $dob, $address, $sex, $race, $dlstatus, $hair, $build);
        $result = mysqli_stmt_execute($stmt);

        if ($result == false)
        {
            die(mysqli_error($link));
        }
    }
    catch(Exception $e)
    {
        die("Failed to run query: " . $e->getMessage()); //TODO: A function to send me an email when this occurs should be made

    }

    $_SESSION['identityMessage'] = '<div class="alert alert-success"><span>Successfully submitted identity request</span></div>';

    sleep(1);
    header("Location:../civilian.php");

}

function create_plate()
{
	session_start();
    $uid = $_SESSION['id'];

    $submittedById = $_SESSION['id'];
    $userId = $_POST['civilian_names'];
    $veh_plate = $_POST['veh_plate'];
    $veh_make = $_POST['veh_make'];
    $veh_model = $_POST['veh_model'];
    $veh_color = $_POST['veh_color'];
    $veh_insurance = $_POST['veh_insurance'];
    $flags = $_POST['flags'];
    $veh_reg_state = $_POST['veh_reg_state'];
    $notes = $_POST['notes'];

    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

    $sql = "INSERT INTO ncic_plates (name_id, veh_plate, veh_make, veh_model, veh_color, veh_insurance, flags, veh_reg_state, notes, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";


	try {
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "isssssssss", $userId, $veh_plate, $veh_make, $veh_model, $veh_color, $veh_insurance, $flags, $veh_reg_state, $notes, $submittedById);
		$result = mysqli_stmt_execute($stmt);

		if ($result == FALSE) {
			die(mysqli_error($link));
		}
	}
	catch (Exception $e)
	{
		die("Failed to run query: " . $e->getMessage()); //TODO: A function to send me an email when this occurs should be made
	}
	mysqli_close($link);

    session_start();
    $_SESSION['plateMessage'] = '<div class="alert alert-success"><span>Successfully added plate to the database</span></div>';

    header("Location:../civilian.php");
}

?>
