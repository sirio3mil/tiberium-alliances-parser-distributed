require 'rubygems'
require 'zmq'


context = ZMQ::Context.new
fe_tasks = context.socket ZMQ::DEALER
# We use a string identity for ease here
fe_tasks.setsockopt ZMQ::IDENTITY, sprintf("%04X-%04X", rand(10000), rand(10000))
fe_tasks.connect 'tcp://localhost:5556'

p "ok"
fe_tasks.send '1111111111111'
p "ok2"
sleep 1
