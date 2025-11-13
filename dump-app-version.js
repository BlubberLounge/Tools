import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

// Convert __dirname to work in ES modules
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const envFilePath = path.resolve(__dirname, '.env');
const envExampleFilePath = path.resolve(__dirname, '.env.example');

// Function to increment the minor version
function incrementMinorVersion(version) {
    const [major, minor, patch, build] = version.split('.').map(Number);
    return `${major}.${Math.max(minor + 1, 0)}.${patch}.${build}`;
}

function incrementBuildVersion(version) {
    const [major, minor, patch, build] = version.split('.').map(Number);
    return `${major}.${minor}.${patch}.${Math.max(build + 1, 0)}`;
}

const timestamp = new Date().toISOString();
let envContent = fs.existsSync(envFilePath) ? fs.readFileSync(envFilePath, 'utf-8') : '';
let currentVersionMatch = envContent.match(/APP_VERSION=(\d+\.\d+\.\d+\.\d+)/);
let newVersion;

if (currentVersionMatch) {
    const currentVersion = currentVersionMatch[1];
    // newVersion = incrementMinorVersion(currentVersion);
    newVersion = incrementBuildVersion(currentVersion);
} else {
    // Default to 1.0.0 if APP_VERSION does not exist
    newVersion = '0.0.0.1';
}

const versionString = `APP_VERSION=${newVersion}`;
const timestampString = `APP_VERSION_TIMESTAMP=${timestamp}`;
const lines = envContent.split('\n');
const appVersionIndex = lines.findIndex(line => line.startsWith('APP_VERSION='));

if (appVersionIndex !== -1) {
    // Replace APP_VERSION with the new version string
    lines[appVersionIndex] = versionString;

    // Insert APP_VERSION_TIMESTAMP right after APP_VERSION
    if (lines[appVersionIndex + 1] && lines[appVersionIndex + 1].startsWith('APP_VERSION_TIMESTAMP=')) {
        // If APP_VERSION_TIMESTAMP already exists after APP_VERSION, replace it
        lines[appVersionIndex + 1] = timestampString;
    } else {
        // Otherwise, insert APP_VERSION_TIMESTAMP as a new line
        lines.splice(appVersionIndex + 1, 0, timestampString);
    }
} else {
    // If APP_VERSION doesn't exist, find APP_URL or add both at the end
    const appUrlIndex = lines.findIndex(line => line.startsWith('APP_URL='));

    if (appUrlIndex !== -1) {
        // Insert APP_VERSION and APP_VERSION_TIMESTAMP after APP_URL
        lines.splice(appUrlIndex + 1, 0, versionString, timestampString);
    } else {
        // Append both APP_VERSION and APP_VERSION_TIMESTAMP at the end
        lines.push(versionString, timestampString);
    }
}

const date = new Date(timestamp);
const datetime = new Intl.DateTimeFormat('de-DE', {
  year: 'numeric',
  month: '2-digit',
  day: '2-digit',
  hour: '2-digit',
  minute: '2-digit',
  second: '2-digit'
}).format(date);

// Write the updated content back to the .env file
envContent = lines.join('\n');
fs.writeFileSync(envFilePath, envContent, 'utf-8');
fs.writeFileSync(envExampleFilePath, envContent, 'utf-8');
console.log(`Updated .env APP_VERSION from version: ${currentVersionMatch ?? currentVersion} to ${newVersion} on ${datetime}`);
