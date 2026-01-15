#!/bin/sh

BASE_URL="http://host.docker.internal:8080"

echo "=== Test rejestracji nowego użytkownika ==="

USERNAME="testuser$(date +%s)"
PASSWORD="zaq1@WSX"
EMAIL="$USERNAME@example.com"
GENRE="rock"

RESPONSE=$(curl -s -X POST "$BASE_URL/register" \
    -d "username=$USERNAME" \
    -d "password=$PASSWORD" \
    -d "password2=$PASSWORD" \
    -d "email=$EMAIL" \
    -d "favorite_genre=$GENRE")

echo "Odpowiedź serwera:"
echo "$RESPONSE"

if echo "$RESPONSE" | grep -q "User registered successfully"; then
    echo "✅ Test przeszedł"
else
    echo "❌ Test NIE przeszedł"
fi
