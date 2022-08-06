from pyngrok import ngrok
import threading,time
port = 81

def ngrok_run():
	while True:
		public_url = ngrok.connect(port,return_ngrok_tunnel=True)

		print("\n\t----------------------------\n\n\t"+public_url.public_url+"\n\n\t----------------------------\n")

		save_time = int(time.time())
		while True:
			time.sleep(60*10)
			if((int(time.time())-save_time)>7*60*60):
				break

		try:
			ngrok.disconnect(ngrok.get_tunnels()[0].public_url)
			ngrok.kill()
			print("\tRestarting server\n\n\n\n")
		except Exception:
			pass

if __name__ == '__main__':
	ngrok_run()