import paho.mqtt.client as mqtt
import json
import mysql.connector
from datetime import datetime
import sys

# Función que se llama cuando se conecta al broker
def on_connect(client, userdata, flags, rc):
    print(f"Conectado con código {rc}")
    # Suscribirse a un tópico
    client.subscribe("esp32/entry")

# Función que se llama cuando se recibe un mensaje
def on_message(client, userdata, msg):
    print(f"Mensaje recibido en el tópico {msg.topic}: {msg.payload.decode()}")
    
    # Obtener el UserID desde PHP
    user_id = sys.argv[1]

    # Conexión con la base de datos MySQL
    db_connection = mysql.connector.connect(
        host="localhost",  # Cambiar si es necesario
        user="root",  # Cambiar al nombre de usuario de la base de datos
        password="",  # Cambiar a la contraseña de la base de datos
        database="lockappdb"
    )

    # Verificar la conexión
    if db_connection.is_connected():
        print("Conectado a la base de datos MySQL")
    else:
        print("Error de conexión a la base de datos MySQL")
    # Procesar el mensaje recibido
    msg_payload = msg.payload.decode("utf-8")
    access_result = ""
    if msg_payload == "PIN Principal" or msg_payload == "PIN Temporal":
        access_result = "Exitoso"
    elif msg_payload == "Bloqueo":
        access_result = "No Exitoso"
    else:
        access_result = "Desconocido"

    # Obtener la fecha y hora actual
    access_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')

    # Insertar los datos en la base de datos
    cursor = db_connection.cursor()
    insert_query = "INSERT INTO accesslogs (UserID, LockID, lockType, AccessTime, AccessResult) VALUES (%s, %s, %s, %s, %s)"
    insert_values = (user_id, 1, msg_payload, access_time, access_result)
    cursor.execute(insert_query, insert_values)
    db_connection.commit()
    print("Datos insertados correctamente en la base de datos")

    # Cerrar la conexión con la base de datos
    cursor.close()
    db_connection.close()

# Crear instancia del cliente MQTT
client = mqtt.Client()

# Asignar las funciones de callback
client.on_connect = on_connect
client.on_message = on_message

# Conectar al broker MQTT
client.connect("broker.hivemq.com", 1883, 60)  # Sustituye "broker.mqtt.com" con la dirección de tu broker

# Mantener el cliente en ejecución para recibir mensajes
client.loop_forever()