import fs from 'fs';
import path from 'path';

const root = process.cwd();
const pkgPath = path.resolve(root, 'package.json');
const versionJsonPath = path.resolve(root, 'version.json');

// ---- Parse command line arguments ------------------------------------------

const args = process.argv.slice(2);
const setIndex = args.indexOf('--set');
const isMajor = args.includes('--major');
const isMinor = args.includes('--minor');

// ---- Read current version from package.json --------------------------------

const pkg = JSON.parse(fs.readFileSync(pkgPath, 'utf-8'));
const [major, minor, patch] = pkg.version.split('.').map(Number);

let newVersion;

if (setIndex !== -1 && args[setIndex + 1]) {
    // Set specific version: node dump-app-version.js --set 1.0.0
    newVersion = args[setIndex + 1];
} else if (isMajor) {
    // Bump major: 0.0.0 → 1.0.0
    newVersion = `${major + 1}.0.0`;
} else if (isMinor) {
    // Bump minor: 0.0.0 → 0.1.0
    newVersion = `${major}.${minor + 1}.0`;
} else {
    // Bump patch (default): 0.0.0 → 0.0.1
    newVersion = `${major}.${minor}.${patch + 1}`;
}

// ---- Update package.json ---------------------------------------------------

pkg.version = newVersion;
fs.writeFileSync(pkgPath, JSON.stringify(pkg, null, 4) + '\n', 'utf-8');

// ---- Write version.json ----------------------------------------------------

const versionData = {
    version: newVersion,
    timestamp: new Date().toISOString(),
};

fs.writeFileSync(versionJsonPath, JSON.stringify(versionData, null, 4) + '\n', 'utf-8');

console.log(`Bumped version → ${newVersion}`);
