require 'rubygems'
require 'ffi-rzmq'

workers_number=0
workers_free=0
workers=[]

context = ZMQ::Context.new
be_worker_socket = context.socket ZMQ::ROUTER
be_worker_socket.bind 'tcp://*:5555'

fro_tasks_socket = context.socket ZMQ::DEALER
fro_tasks_socket.bind 'tcp://*:5556'

poller = ZMQ::Poller.new
poller.register fro_tasks_socket, ZMQ::POLLIN
poller.register be_worker_socket, ZMQ::POLLIN

loop do
  ready = poller.poll(workers_number ==0 ? -1 : 1000)
  if ready >0
    if  poller.readables.include? be_worker_socket
      be_worker_socket.recv_strings(data=[])
      address= data[0]
      msg= data[2]
      if msg == "ready"
        be_worker_socket.send_string address, ZMQ::SNDMORE
        be_worker_socket.send_string '', ZMQ::SNDMORE
        be_worker_socket.send_string ''

        workers_number +=1
      end
      if msg == "free"
        p "free worker"
        workers_free +=1
        workers.unshift address
      end
    end

    if workers_free>0 and poller.readables.include? fro_tasks_socket
      fro_tasks_socket.recv_string(m="")
      p m
      be_worker_socket.send_string workers.pop(), ZMQ::SNDMORE
      be_worker_socket.send_string '', ZMQ::SNDMORE
      be_worker_socket.send_string m
      workers_free-=1
    end
  end
end
