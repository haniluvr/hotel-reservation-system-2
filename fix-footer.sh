#!/bin/bash

echo "Clearing Vite cache..."
if [ -d "node_modules/.vite" ]; then
    rm -rf node_modules/.vite
    echo "Vite cache cleared!"
else
    echo "No Vite cache found."
fi

echo ""
echo "Starting Vite dev server..."
echo "Please keep this terminal open and open a NEW terminal for other commands."
echo ""
npm run dev

