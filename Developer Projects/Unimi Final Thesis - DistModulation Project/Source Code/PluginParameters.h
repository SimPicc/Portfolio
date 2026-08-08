#pragma once
#include <JuceHeader.h>

namespace Parameters 
{

	//FUZZ
	static const String nameFuzzPower= "FPW";
	static const String nameFuzzType = "FTY";
	static const String nameFuzzGain = "FGA";
	static const String nameFuzzVolume = "FVO";
	static const String nameFuzzTone = "FTO";
	static const String nameFuzzBias = "FBI";
	static const String nameFuzzHP = "FHP";

	static const bool defaultFuzzPower = true;
	static const int defaultFuzzType = 2;
	static const float defaultFuzzGain = 5.0f;
	static const float defaultFuzzVolume = 0.0f;
	static const float defaultFuzzTone = 0.0f;
	static const float defaultFuzzBias = 0.0f;
	static const bool defaultFuzzHP = true;

	//DELAY
	static const String nameDelayPower = "DPW";
	static const String nameDelayTime = "DDT";
	static const String nameDelayDryWet = "DDW";
	static const String nameDelayFeedback = "DFB";
	static const String nameDelayTone = "DTO";

	static const bool defaultDelayPower = false;
	static const float defaultDelayTime = 0.5f;
	//static const float defaultDelayDryWet = 0.5f;
	static const float defaultDelayDryWet = 0.325f;
	static const float defaultDelayFeedback = 0.0f;
	static const float defaultDelayTone = 0.0f;
	//CHORUS
	static const String nameChorusPower = "CPW";
	static const String nameChorusRate = "CRT";
	static const String nameChorusDepth = "CDPT";
	static const String nameChorusDryWet = "CDW";

	static const bool defaultChorusPower = false;
	static const float defaultChorusRate = 1.0f;
	static const float defaultChorusDepth = 0.0f;
	static const float defaultChorusDryWet = 0.5f;

	//CHAIN
	static const String nameChain = "CH";
	static const int defaultChain = 0;

	static AudioProcessorValueTreeState::ParameterLayout createParameterLayout()
	{
		std::vector<std::unique_ptr<RangedAudioParameter>> params;
		int id = 1;
		//FUZZ
		params.push_back(std::make_unique<AudioParameterBool>((ParameterID(nameFuzzPower, id)), "Power", defaultFuzzPower));
		params.push_back(std::make_unique<AudioParameterChoice>((ParameterID(nameFuzzType, id++)), "Type", StringArray{ "Overdrive","Lead","Fuzz" }, defaultFuzzType));
		params.push_back(std::make_unique<AudioParameterFloat>((ParameterID(nameFuzzGain, id++)), "Gain", NormalisableRange<float>(0.5f, 10.0f, 0.01f, 0.92f), defaultFuzzGain));
		params.push_back(std::make_unique<AudioParameterFloat>((ParameterID(nameFuzzVolume, id++)), "Volume", juce::NormalisableRange<float>(-48.0f,12.0f,0.01f,3.106f), defaultFuzzVolume));
		params.push_back(std::make_unique<AudioParameterFloat>((ParameterID(nameFuzzTone, id++)), "Tone", -1.0f, 1.0f, defaultFuzzTone));
		params.push_back(std::make_unique<AudioParameterFloat>((ParameterID(nameFuzzBias, id++)), "Bias", 0.0f, 1.0f, defaultFuzzBias));
		params.push_back(std::make_unique<AudioParameterBool>((ParameterID(nameFuzzHP, id)), "HP", defaultFuzzHP));
		//DELAY
		params.push_back(std::make_unique<AudioParameterBool>(ParameterID(nameDelayPower, id++), "Power", defaultDelayPower));
		params.push_back(std::make_unique<AudioParameterFloat>(ParameterID(nameDelayTime, id++), "Time", NormalisableRange<float>(0.0f, 2.0f, 0.001, 0.5), defaultDelayTime));
		//params.push_back(std::make_unique<AudioParameterFloat>(ParameterID(nameDelayDryWet, id++), "DryWet", 0.0f, 1.0f, defaultDelayDryWet));
		params.push_back(std::make_unique<AudioParameterFloat>(ParameterID(nameDelayDryWet, id++), "DryWet", 0.0f, 0.65f, defaultDelayDryWet));
		params.push_back(std::make_unique<AudioParameterFloat>(ParameterID(nameDelayFeedback, id++), "Repeat", NormalisableRange<float>(0.0f, 1.0f, 0.001, 1.5), defaultDelayFeedback));
		params.push_back(std::make_unique<AudioParameterFloat>(ParameterID(nameDelayTone, id++), "Tone", -1.0f, 1.0f, defaultDelayTone));
		//CHORUS
		params.push_back(std::make_unique<AudioParameterBool>(ParameterID(nameChorusPower, id++), "Power", defaultChorusPower));
		params.push_back(std::make_unique<AudioParameterFloat>(ParameterID(nameChorusRate, id++), "Rate", NormalisableRange<float>(0.1f, 10.0f, 0.01, 0.29), defaultChorusRate));
		params.push_back(std::make_unique<AudioParameterFloat>(ParameterID(nameChorusDepth, id++), "Depth", NormalisableRange<float>(0.0f, 100.0f, 0.1, 0.5), defaultChorusDepth));
		params.push_back(std::make_unique<AudioParameterFloat>(ParameterID(nameChorusDryWet, id++), "DryWet", 0.0f, 1.0f, defaultChorusDryWet));
		//CHAIN
		params.push_back(std::make_unique<AudioParameterChoice>((ParameterID(nameChain, id++)), "Chain", StringArray{ "F-C-D","F-D-C","C-F-D","C-D-F","D-F-C","D-C-F" }, defaultChain));

		return { params.begin(),params.end() };
	}





	static void addListenerToAllParameters(AudioProcessorValueTreeState& valueTreeState, AudioProcessorValueTreeState::Listener* listener)
	{
		std::unique_ptr<XmlElement> xml(valueTreeState.copyState().createXml());

		for (auto* element : xml->getChildWithTagNameIterator("PARAM"))
		{
			const String& id = element->getStringAttribute("id");
			valueTreeState.addParameterListener(id, listener);
		}
	}
}
