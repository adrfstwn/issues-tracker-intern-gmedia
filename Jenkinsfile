pipeline {
    agent any
    environment {
        IMAGE_TAG = "${env.BUILD_ID}"
        GIT_BRANCH = "main"
        DOCKER_IMAGE_NAME_BACKEND = "issues-tracker-backend"
        DOCKER_IMAGE_NAME_FRONTEND = "issues-tracker-frontend"
    }
    stages {
        stage('Checkout Code') {
            steps {
                script {
                    sh """
                    echo "Cleaning workspace..."
                    rm -rf issues-tracker-intern-gmedia || true
                    rm -rf issue-tracker-intern-gmedia || true
                    """
                    withCredentials([usernamePassword(credentialsId: 'github-auth-to-jenkins', usernameVariable: 'GIT_USER', passwordVariable: 'GIT_TOKEN')]) {
                        sh """
                        echo "Cloning repository with authentication..."
                        git clone https://${GIT_USER}:${GIT_TOKEN}@github.com/adrfstwn/issues-tracker-intern-gmedia.git -b ${GIT_BRANCH}
                        """
                    }
                }
            }
        }
        stage('Prepare .env File') {
            steps {
                script {
                    withCredentials([file(credentialsId: 'issues-tracker-backend-env', variable: 'ENV_FILE')]) {
                        sh """
                        echo "Creating .env file fro backend..."
                        cp \${ENV_FILE} issues-tracker-intern-gmedia/backend-api/.env
                        """
                    }
                    withCredentials([file(credentialsId: 'issues-tracker-frontend-env', variable: 'ENV_FILE')]) {
                        sh """
                        echo "Creating .env file for frontend..."
                        cp \${ENV_FILE} issues-tracker-intern-gmedia/vue/.env
                        """
                    }
                }
            }
        }
        stage('Stop Running Containers & Remove Old Images') {
            steps {
                script {
                    sh """
                    echo "Stopping running containers..."
                    
                    cd issues-tracker-intern-gmedia
                    
                    docker compose down || true
                    
                    echo "Checking and removing old backend and frontend images..."
                    
                    OLD_BACKEND_IMAGE=\$(docker images -q ${DOCKER_IMAGE_NAME_BACKEND})
                    OLD_FRONTEND_IMAGE=\$(docker images -q ${DOCKER_IMAGE_NAME_FRONTEND})

                    if [ ! -z "\$OLD_BACKEND_IMAGE" ]; then
                        echo "Deleting old backend image..."
                        docker rmi -f \$OLD_BACKEND_IMAGE
                    fi

                    if [ ! -z "\$OLD_FRONTEND_IMAGE" ]; then
                        echo "Deleting old frontend image..."
                        docker rmi -f \$OLD_FRONTEND_IMAGE
                    fi

                    echo "Finished cleaning up old images."
                    """
                }
            }
        }
        stage('Build & Deploy Docker Images') {
            steps {
                script {
                    sh """
                    echo "Updating docker-compose.yml with new image tags..."

                    cd issues-tracker-intern-gmedia

                    # Update image tag untuk backend
                    sed -i "s|image: issues-tracker-backend:latest|image: issues-tracker-backend:${IMAGE_TAG}|" docker-compose.yml

                    # Update image tag untuk frontend
                    sed -i "s|image: issues-tracker-frontend:latest|image: issues-tracker-frontend:${IMAGE_TAG}|" docker-compose.yml

                    echo "Final docker-compose.yml content:"
                    cat docker-compose.yml

                    echo "Building Docker images..."

                    # Build ulang dengan tag baru
                    docker compose build --no-cache

                    echo "Deploying containers..."
                    docker compose up -d --force-recreate

                    echo "Checking running containers..."
                    docker ps -a

                    echo "Checking backend working directory..."
                    docker exec issues-tracker-backend pwd
                    docker exec issues-tracker-backend ls -al /app

                    echo "Checking frontend working directory..."
                    docker exec issues-tracker-frontend pwd
                    docker exec issues-tracker-frontend ls -al /app
                    """
                }
            }
        }

    }
    post {
        success {
            echo "Pipeline completed successfully!"
        }
        failure {
            echo "Pipeline failed!"
        }
    }
}