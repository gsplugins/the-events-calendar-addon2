#!/bin/bash

PLUGIN="the-events-calendar-addon"
BUILD="build"

echo "Cleaning old build..."
rm -rf $BUILD

mkdir $BUILD

echo "Copy plugin files..."
rsync -av --exclude=node_modules \
          --exclude=.git \
          --exclude=src \
          --exclude=build \
          ./ $BUILD/

echo "Creating zip..."

cd $BUILD
zip -r ../$PLUGIN.zip .
cd ..

echo "Build completed!"