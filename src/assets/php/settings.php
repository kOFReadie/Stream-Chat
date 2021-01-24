<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/../api/account/DBDetails.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/../api/account/accountFunctions.php';

$query = json_decode($_POST["q"], true);
$response = new stdClass();
$unid = $query["unid"];

if (LogIn($query["unid"], $query["pass"])) //This function does exist just not in this directory so intelisense says its bad
{
    if (isset($query["update_twitch"])) //Query has page count ? get query type : return null
    {
        $twitch_username = $query["update_twitch"]["twitch_username"];
        $twitch_oauth = $query["update_twitch"]["twitch_oauth"];

        $sql = "INSERT INTO stream_chat(
            unid,
            twitch_username,
            twitch_oauth,
            alteredDate
        )
        VALUES(
            '$unid',
            '$twitch_username',
            '$twitch_oauth',
            current_timestamp()
        )";

        $result = mysqli_query($conn, $sql);

        if ($result === TRUE) { $response->result = "Successfully updated twitch credentials"; }
        else { $response->result = "Failed to update twitch credentials"; }
    }
    else if (isset($query["get_all"]))
    {
        $sql = "SELECT settings.twitch_username, settings.twitch_oauth, settings.tts_mode, settings.tts_voice, settings.tts_filters_enabled, settings.tts_filters, settings.aws_region, settings.aws_identity_pool, settings.stt_enabled, settings.stt_listeners
        FROM users usr
        LEFT JOIN stream_chat settings
        ON settings.unid = usr.unid
        WHERE settings.unid = '$unid'";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0)
        {
            $sqlResults = new stdClass();
            $row = mysqli_fetch_assoc($result);
            $sqlResults->unid = $unid;
            $sqlResults->twitch_username = $row["twitch_username"];
            $sqlResults->twitch_oauth = $row["twitch_oauth"]; //These keys should probably be encrypted in the database but with my current method the decryption does not return the orignal value, only a boolean.
            $sqlResults->tts_mode = $row["tts_mode"];
            $sqlResults->tts_voice = $row["tts_voice"];
            $sqlResults->tts_filters_enabled = $row["tts_filters_enabled"];
            $sqlResults->tts_filters = $row["tts_filters"];
            $sqlResults->aws_region = $row["aws_region"];
            $sqlResults->aws_identity_pool = $row["aws_identity_pool"];
            $sqlResults->stt_enabled = $row["stt_enabled"];
            $sqlResults->stt_listeners = $row["stt_listeners"];
            $response->result = $sqlResults;
        }
        else { $response->result = null; }
    }
    else { $response->result = "Invalid Request"; }
}
else { $response->result = "Account details invalid"; }

echo json_encode($response);