<?php
require 'vendor/autoload.php'; // Composer autoload

use Telegram\Bot\Api;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize Telegram Bot API
$telegram = new Api($_ENV['TELEGRAM_BOT_TOKEN']);

// Handle incoming updates
$update = $telegram->getWebhookUpdate();

if (!$update->has('message')) {
    exit; // Ignore non-message updates
}

$message = $update->getMessage();
$chatId = $message->getChat()->getId();
$text = $message->getText();

// Validate and sanitize user input
$text = trim($text); // Remove leading/trailing whitespace
$text = strtolower($text); // Normalize input to lowercase

// Command handler
switch ($text) {
    case '/start':
        $response = "Welcome to Pump Bot! Use /help to see available commands.";
        break;
    case '/help':
        $response = "Available commands:\n"
                  . "/connectwallet - Connect your wallet\n"
                  . "/selecttoken - Choose a token to pump\n"
                  . "/setduration - Set pumping duration\n"
                  . "/setgasfee - Set gas fee preset\n"
                  . "/startpump - Start the bot\n"
                  . "/stoppump - Stop the bot";
        break;
    case '/connectwallet':
        $response = connectWallet('phantom'); // Default to Phantom Wallet for now
        break;
    case '/selecttoken':
        $response = selectToken('SOL'); // Default to SOL for now
        break;
    case '/setduration':
        $response = setDuration('10m'); // Default to 10 minutes for now
        break;
    case '/setgasfee':
        $response = setGasFee('minimum'); // Default to minimum gas fee for now
        break;
    case '/startpump':
        $response = toggleBotState('on');
        break;
    case '/stoppump':
        $response = toggleBotState('off');
        break;
    default:
        $response = "Invalid command. Use /help to see available commands.";
        break;
}

// Send response
try {
    $telegram->sendMessage([
        'chat_id' => $chatId,
        'text' => $response,
    ]);
} catch (Exception $e) {
    // Log the error and notify the user
    error_log("Error sending message: " . $e->getMessage());
    $telegram->sendMessage([
        'chat_id' => $chatId,
        'text' => "An error occurred. Please try again later.",
    ]);
}

// Wallet Integration
function connectWallet($walletType) {
    $walletType = strtolower(trim($walletType)); // Normalize input
    switch ($walletType) {
        case 'phantom':
            return "Connecting to Phantom Wallet... (Placeholder for Phantom API integration)";
        default:
            return "Unsupported wallet type.";
    }
}

// Token Pumping Logic
function selectToken($token) {
    $token = strtoupper(trim($token)); // Normalize input
    $supportedTokens = ['SOL', 'USDC', 'DUMMY'];
    if (in_array($token, $supportedTokens)) {
        return "Selected token: $token";
    } else {
        return "Unsupported token.";
    }
}

function setDuration($duration) {
    $duration = strtolower(trim($duration)); // Normalize input
    $validDurations = ['10m', '1h', 'custom'];
    if (in_array($duration, $validDurations)) {
        return "Pumping duration set to: $duration";
    } else {
        return "Invalid duration.";
    }
}

// Gas Fee Settings
function setGasFee($preset) {
    $preset = strtolower(trim($preset)); // Normalize input
    $validPresets = ['minimum', 'fast', 'turbo'];
    if (in_array($preset, $validPresets)) {
        return "Gas fee preset set to: $preset";
    } else {
        return "Invalid gas fee preset.";
    }
}

// On/Off Control
$botState = 'off'; // Default state

function toggleBotState($state) {
    global $botState;
    $state = strtolower(trim($state)); // Normalize input
    if ($state === 'on' || $state === 'off') {
        $botState = $state;
        return "Bot is now $state.";
    } else {
        return "Invalid state.";
    }
}

// Error Handling
function handleError($error) {
    error_log("Error: " . $error); // Log the error
    return "An error occurred. Please try again later.";
}
