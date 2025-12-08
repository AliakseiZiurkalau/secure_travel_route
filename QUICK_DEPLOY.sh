#!/bin/bash
# Быстрая загрузка TravelPi на GitHub

echo "🚀 TravelPi - Загрузка на GitHub"
echo ""

# Проверка, что мы в git репозитории
if [ ! -d .git ]; then
    echo "❌ Ошибка: это не git репозиторий"
    exit 1
fi

# Запрос имени пользователя GitHub
read -p "Введите ваше имя пользователя GitHub: " GITHUB_USER

if [ -z "$GITHUB_USER" ]; then
    echo "❌ Имя пользователя не может быть пустым"
    exit 1
fi

# Запрос названия репозитория
read -p "Введите название репозитория (по умолчанию: travelpi): " REPO_NAME
REPO_NAME=${REPO_NAME:-travelpi}

echo ""
echo "📋 Параметры:"
echo "   Пользователь: $GITHUB_USER"
echo "   Репозиторий: $REPO_NAME"
echo ""

# Выбор протокола
echo "Выберите протокол:"
echo "1) HTTPS"
echo "2) SSH"
read -p "Ваш выбор (1 или 2): " PROTOCOL

if [ "$PROTOCOL" = "2" ]; then
    REMOTE_URL="git@github.com:$GITHUB_USER/$REPO_NAME.git"
else
    REMOTE_URL="https://github.com/$GITHUB_USER/$REPO_NAME.git"
fi

echo ""
echo "🔗 URL репозитория: $REMOTE_URL"
echo ""

# Проверка существования remote
if git remote | grep -q "^origin$"; then
    echo "⚠️  Remote 'origin' уже существует"
    read -p "Удалить и пересоздать? (y/n): " RECREATE
    if [ "$RECREATE" = "y" ]; then
        git remote remove origin
        echo "✅ Remote 'origin' удален"
    else
        echo "❌ Отменено"
        exit 1
    fi
fi

# Добавление remote
echo "➕ Добавление remote..."
git remote add origin "$REMOTE_URL"

if [ $? -eq 0 ]; then
    echo "✅ Remote добавлен успешно"
else
    echo "❌ Ошибка добавления remote"
    exit 1
fi

# Проверка ветки
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "main" ]; then
    echo "🔄 Переименование ветки в 'main'..."
    git branch -M main
fi

# Загрузка на GitHub
echo ""
echo "📤 Загрузка на GitHub..."
git push -u origin main

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Проект успешно загружен на GitHub!"
    echo ""
    echo "🌐 Откройте репозиторий:"
    echo "   https://github.com/$GITHUB_USER/$REPO_NAME"
    echo ""
    echo "📝 Не забудьте:"
    echo "   1. Добавить описание репозитория"
    echo "   2. Добавить topics (теги)"
    echo "   3. Загрузить скриншоты"
    echo ""
else
    echo ""
    echo "❌ Ошибка загрузки на GitHub"
    echo ""
    echo "Возможные причины:"
    echo "   1. Репозиторий не создан на GitHub"
    echo "   2. Нет прав доступа"
    echo "   3. Неверный URL"
    echo ""
    echo "Создайте репозиторий вручную:"
    echo "   https://github.com/new"
    echo ""
    echo "Затем выполните:"
    echo "   git push -u origin main"
    exit 1
fi
